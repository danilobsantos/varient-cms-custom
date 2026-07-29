<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Services\SitemapService;
use App\Services\RssService;

class CronController extends Controller
{
    protected $cache;
    protected $config;

    // CRON INTERVAL CONSTANTS (IN SECONDS)
    private const INTERVAL_SHOWCASE = 10800; // 3 Hours
    private const INTERVAL_AUTO_DELETE = 10800; // 3 Hours
    private const INTERVAL_SUBSCRIPTION_CHECK = 3600; // 1 Hour

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        helper('text');

        $this->cache = \Config\Services::cache();

        $this->config = getContextValue('config');
    }

    /**
     * Master Cron Service
     * Route: service/cron/main
     * Subscription Route: service/cron/subscription
     */
    public function run($type = null)
    {
        // Security Check
        $requestToken = inputGet('token');
        $systemToken = $this->config->cron_secret_key;

        if (!empty($systemToken) && $systemToken !== $requestToken) {
            return $this->response->setStatusCode(401)->setBody('Unauthorized: Invalid Token');
        }

        // DYNAMIC LOCK KEY & DURATION
        $isSubscription = (!empty($type) && $type === 'subscription');
        $lockKey = $isSubscription ? 'cron_subscription_lock' : 'cron_service_lock';
        $lockDuration = $isSubscription ? 300 : 55; // Subscriptions get 5 mins due to SMTP, normal gets 55s

        // Rate Limiting
        if ($this->cache->get($lockKey)) {
            return $this->response->setStatusCode(429)->setBody("Skipped: Cron ($lockKey) is already running.");
        }

        // Set lock
        $this->cache->save($lockKey, true, $lockDuration);

        $output = [];

        try {

            if ($isSubscription) {

                // Check subscriptions
                $result = $this->processSubscriptionChecks();
                if (!empty($result)) {
                    $output[] = $result;
                }

            } else {

                // Check scheduled posts
                $pubCount = $this->processScheduledPosts();
                if ($pubCount > 0) {
                    $output[] = "Published $pubCount scheduled posts.";
                }

                // Generate sitemap
                $sitemapUpdated = $this->processSitemapUpdate();
                if ($sitemapUpdated) {
                    $output[] = "Sitemap regenerated.";
                } else {
                    $output[] = "Sitemap is fresh. Skipped.";
                }

                // Showcase Posts Cleanup
                $showcasePostsResult = $this->processShowcasePostsCleanup();
                if ($showcasePostsResult) {
                    $output[] = $showcasePostsResult;
                }

                // Old Posts Cleanup
                $oldPostsResult = $this->processOldPostCleanup();
                if ($oldPostsResult) {
                    $output[] = $oldPostsResult;
                }

                // RSS Queue
                if (!$sitemapUpdated) {
                    $output[] = $this->processRssQueue();
                }

            }

            // RELEASE LONG LOCKS EARLY IF FINISHED FASTER
            if ($isSubscription) {
                $this->cache->delete($lockKey);
            }

            return $this->response->setContentType('text/plain')->setBody(implode("\n", $output));

        } catch (\Throwable $e) {
            // Ensure lock is released even if it crashes
            if ($isSubscription) {
                $this->cache->delete($lockKey);
            }
            return $this->response->setStatusCode(500)->setBody('Error: ' . $e->getMessage());
        }
    }

    /**
     * Process Scheduled Posts
     */
    private function processScheduledPosts()
    {
        $postModel = model('PostModel');

        $posts = $postModel
            ->where('is_scheduled', 1)
            ->where('status', 1)
            ->where('scheduled_at <=', dateTimeNow())
            ->findAll(20);

        if (empty($posts)) return 0;

        $count = 0;
        foreach ($posts as $post) {
            try {
                if ($postModel->publishPosts((int)$post->id, 'scheduled')) {
                    $count++;
                }
            } catch (\Exception $e) {
                log_message('error', '[Cron] Failed to publish scheduled post ID ' . $post->id . '. Error: ' . $e->getMessage());
                continue;
            }
        }

        return $count;
    }

    /**
     * Process Update Sitemap
     */
    private function processSitemapUpdate()
    {
        $filePath = FCPATH . 'sitemap.xml';

        if (file_exists($filePath)) {

            // Clear PHP file status cache to get real-time data
            clearstatcache(true, $filePath);

            // Calculate file age
            $fileAge = time() - filemtime($filePath);

            if ($fileAge < SITEMAP_REFRESH_INTERVAL) {
                return false;
            }
        }

        try {
            $service = new SitemapService($this->config);
            $service->generate();

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Process Showcase Posts Cleanup
     */
    private function processShowcasePostsCleanup(): ?string
    {
        $key = 'cron_last_showcase_posts_cleanup';

        $lastRun = $this->cache->get($key);

        // Check if the defined interval has passed since the last run
        if ($lastRun && (time() - $lastRun < self::INTERVAL_SHOWCASE)) {
            return null;
        }

        try {
            // Execute the cleanup and get the count of deleted rows
            $deletedCount = model('PostSelectionModel')->deleteExpiredSelections();

            // Save the current time to cache (expires in double the interval to be safe)
            $this->cache->save($key, time(), self::INTERVAL_SHOWCASE * 2);

            return "Showcase Posts Cleanup: Purged {$deletedCount} old records.";

        } catch (\Exception $e) {
            return "Showcase Posts Cleanup: Failed.";
        }
    }

    /**
     * Process Old Post Cleanup
     */
    private function processOldPostCleanup(): ?string
    {
        if ((int)($this->config->auto_post_deletion_settings->status ?? 0) !== 1) {
            return null;
        }

        $key = 'cron_last_auto_post_deletion';

        $lastRun = $this->cache->get($key);

        // Check if the defined interval has passed since the last run
        if ($lastRun && (time() - $lastRun < self::INTERVAL_AUTO_DELETE)) {
            return null; // Skip execution
        }

        try {
            // Execute the cleanup with a safe limit of 50
            $deletedCount = model('PostModel')->deleteOldPosts(50);

            // Save the current time to cache (expires in double the interval to be safe)
            $this->cache->save($key, time(), self::INTERVAL_AUTO_DELETE * 2);

            // Only log an output if something was actually deleted
            if ($deletedCount > 0) {
                return "Auto Post Deletion: Purged {$deletedCount} old records.";
            }

            return null;

        } catch (\Exception $e) {
            return "Auto Post Deletion: Failed.";
        }
    }

    /**
     * Process RSS Queue
     */
    private function processRssQueue()
    {
        $rssModel = model('RssModel');

        // Fetch 3 oldest checked feeds
        $feeds = $rssModel->where('auto_update', 1)
            ->orderBy('last_checked_at', 'ASC')
            ->findAll(3);

        if (empty($feeds)) return "RSS Queue: Empty.";

        $processed = 0;
        $rssService = new RssService();

        foreach ($feeds as $feed) {
            try {

                $rssService->importFeed($feed);
                $processed++;

            } catch (\Exception $e) {
            } finally {
                // Always update timestamp to rotate the queue
                $rssModel->update($feed->id, ['last_checked_at' => dateTimeNow()]);
            }
        }

        return "RSS Queue: Processed $processed feeds.";
    }

    /**
     * Process Subscription Checks for Expirations and Reminders
     */
    private function processSubscriptionChecks(): ?string
    {
        $key = 'cron_last_subscription_check';
        $lastRun = $this->cache->get($key);

        // Check if the defined interval (1 hour) has passed since the last run
        if ($lastRun && (time() - $lastRun < self::INTERVAL_SUBSCRIPTION_CHECK)) {
            return null;
        }

        try {
            $subscriptionModel = model('UserSubscriptionModel');
            $activeSubs = $subscriptionModel->getActiveSubscriptionsWithExpiry();

            if (empty($activeSubs)) {
                return "Subscription Check: No action.";
            }

            $processedCount = 0;
            $paymentService = new \App\Services\PaymentService();
            $now = timeNowObject();

            foreach ($activeSubs as $sub) {
                // Parse the expiration date robustly, respecting the application's timezone
                $expiresAt = \CodeIgniter\I18n\Time::parse($sub->expires_at);

                // Absolute difference in seconds
                $timeDiff = $expiresAt->getTimestamp() - $now->getTimestamp();

                // Check for Expiration
                if ($timeDiff <= 0) {

                    // Gateway-Linked Subscriptions (Stripe, PayPal) -> Require a 24-hour Grace Period
                    if (!empty($sub->gateway_subscription_id)) {
                        if ($timeDiff <= -86400) {
                            $paymentService->updateSubscriptionStatus($sub->gateway_subscription_id, 'expired');
                            $processedCount++;
                        }
                        continue;
                    } // Manual Subscriptions -> Expire Immediately
                    else {
                        $subscriptionModel->update($sub->id, [
                            'status'     => 'expired',
                            'updated_at' => $now->toDateTimeString()
                        ]);
                        sendPremiumEmail($sub->user_email, 'subscription_expired');
                        $processedCount++;
                        continue;
                    }
                }

                // Check for 1-Day Reminder (<= 24 hours / 86400 seconds)
                if ($timeDiff <= 86400 && (int)$sub->reminder_1_day_sent === 0) {
                    sendPremiumEmail($sub->user_email, 'expiring_today');
                    $subscriptionModel->update($sub->id, ['reminder_1_day_sent' => 1]);
                    $processedCount++;
                    continue;
                }

                // Check for 3-Days Reminder (<= 72 hours / 259200 seconds)
                if ($timeDiff <= 259200 && (int)$sub->reminder_3_days_sent === 0) {
                    sendPremiumEmail($sub->user_email, 'expiring_soon');
                    $subscriptionModel->update($sub->id, ['reminder_3_days_sent' => 1]);
                    $processedCount++;
                    continue;
                }
            }

            // Save the current timestamp to cache
            $this->cache->save($key, $now->getTimestamp(), self::INTERVAL_SUBSCRIPTION_CHECK * 2);

            if ($processedCount > 0) {
                return "Subscription Check: Processed {$processedCount} events (Expirations/Reminders).";
            }

            return "Subscription Check: No action.";


        } catch (\Exception $e) {
            log_message('error', '[Cron] Subscription Check Failed: ' . $e->getMessage());
            return "Subscription Check: Failed.";
        }
    }
}