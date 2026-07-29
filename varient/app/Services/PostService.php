<?php

declare(strict_types=1);

namespace App\Services;

use App\Traits\CsvExportArrayTrait;

/**
 * Class PostService
 *
 * Handles post-related business logic including view counting,
 * reward system calculations, and preparing post items/relations.
 */
class PostService
{
    // Include the new array-based CSV export trait
    use CsvExportArrayTrait;

    /**
     * The application's cache service instance
     */
    protected CacheService $cacheService;

    /**
     * Main config object
     */
    protected $config;

    /**
     * Inject the dependencies (passed from Config/Services.php).
     *
     * @param CacheService $cacheService The shared instance of CacheService.
     */
    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;

        $this->config = getContextValue('config');
    }

    /**
     * Get latest posts
     */
    public function getLatestPosts(int $langId, int $perPage, int $offset): ?array
    {
        return $this->cacheService->get(
            sprintf('latest_posts_lang_%d_limit_%d_offset_%d', $langId, $perPage, $offset),
            fn() => model('PostModel')->getLatestPosts($langId, $perPage, $offset) ?? [],
            'content'
        );
    }

    /**
     * Get latest posts by categories
     */
    public function getLatestPostsByCategories(int $langId, ?array $categoryTree): ?array
    {
        return $this->cacheService->get(
            'latest_posts_by_categories_lang_' . $langId,
            fn() => model('PostModel')->getLatestPostsByCategories($langId, $categoryTree),
            'content'
        );
    }

    /**
     * Retrieves showcase posts
     */
    public function getShowcasePosts(int $langId): ?array
    {
        return $this->cacheService->get(
            'showcase_posts_lang_' . $langId,
            fn() => model('PostModel')->getShowcasePosts($langId),
            'content'
        );
    }

    /**
     * Retrieves slider posts
     */
    public function getSliderPosts(array $postSelections, array $latestPosts): array
    {
        $settings = $this->config->featured_content_settings ?? new \stdClass();

        $source = $settings->slider_source ?? 'manuel';
        $limit = (int)($settings->slider_limit ?? 15);
        $sort = $settings->slider_sorting ?? 'slider_order'; // slider_order or by_date

        // Filter the correct source array
        if ($source === 'latest') {
            $posts = $latestPosts;
        } else {
            $posts = array_filter($postSelections, function ($post) {
                return isset($post->selection_type) && $post->selection_type === 'slider';
            });
        }

        // Apply sorting using our new smart helper
        if ($sort === 'slider_order') {
            $posts = sortArrayOfObjects($posts, 'slider_order', 'asc');
        } else {
            // Sort by creation date (newest first)
            $posts = sortArrayOfObjects($posts, 'created_at', 'desc');
        }

        // Apply limit and reset array keys
        return array_slice($posts, 0, $limit > 0 ? $limit : 20);
    }

    /**
     * Retrieves featured posts
     */
    public function getFeaturedPosts(array $postSelections, array $latestPosts, array $sliderPostIds = []): array
    {
        $settings = $this->config->featured_content_settings ?? new \stdClass();

        $source = $settings->featured_source ?? 'manuel';
        $limit = (int)($settings->featured_limit ?? 20);
        $sort = $settings->featured_sorting ?? 'featured_order'; // featured_order or by_date

        // Check if the admin wants to hide slider posts in the featured section
        $excludeSliderPosts = !empty($settings->exclude_slider_posts);

        // Filter the correct source array
        if ($source === 'latest') {
            $posts = $latestPosts;

            // If using latest posts, still apply the exclusion logic if enabled
            if ($excludeSliderPosts && !empty($sliderPostIds)) {
                $posts = array_filter($posts, function ($post) use ($sliderPostIds) {
                    return isset($post->id) && !in_array($post->id, $sliderPostIds);
                });
            }
        } else {
            // Filter the selections array to get only featured posts
            $posts = array_filter($postSelections, function ($post) use ($sliderPostIds, $excludeSliderPosts) {

                // Check if it is a featured post
                $isFeatured = isset($post->selection_type) && $post->selection_type === 'featured';
                if (!$isFeatured) {
                    return false;
                }

                // Exclude if the post is in the slider AND the admin setting is enabled
                if ($excludeSliderPosts && !empty($sliderPostIds) && isset($post->id) && in_array($post->id, $sliderPostIds)) {
                    return false;
                }

                return true;
            });
        }

        // Apply sorting
        if ($sort === 'featured_order' && $source !== 'latest') {
            $posts = sortArrayOfObjects($posts, 'featured_order', 'asc');
        } else {
            // Sort by creation date (newest first)
            $posts = sortArrayOfObjects($posts, 'created_at', 'desc');
        }

        // Apply limit and reset array keys
        return array_slice($posts, 0, $limit > 0 ? $limit : 4);
    }

    /**
     * Retrieves recommended posts
     */
    public function getRecommendedPosts(array $postSelections): array
    {
        $settings = $this->config->featured_content_settings ?? new \stdClass();

        $limit = (int)($settings->recommended_limit ?? 5);
        $sort = $settings->recommended_sorting ?? 'by_date'; // by_date or random

        // Filter the selections array to get only recommended posts
        $posts = array_filter($postSelections, function ($post) {
            return isset($post->selection_type) && $post->selection_type === 'recommended';
        });

        // Apply sorting based on admin preference
        if ($sort === 'random') {
            // Randomize the array order
            shuffle($posts);
        } else {
            // Sort by creation date (newest first)
            $posts = sortArrayOfObjects($posts, 'created_at', 'desc');
        }

        // Apply limit and reset array keys
        return array_slice($posts, 0, $limit > 0 ? $limit : 5);
    }

    /**
     * Retrieves breaking news posts
     */
    public function getBreakingNews(array $postSelections, array $latestPosts): array
    {
        $settings = $this->config->featured_content_settings ?? new \stdClass();

        $source = $settings->br_news_source ?? 'manuel';
        $limit = (int)($settings->br_news_limit ?? 15);
        $sort = $settings->br_news_sorting ?? 'by_date'; // by_date or random

        // Filter the correct source array based on admin settings
        if ($source === 'latest') {
            $posts = $latestPosts;
        } else {
            // Filter the selections array to get only breaking news posts
            $posts = array_filter($postSelections, function ($post) {
                return isset($post->selection_type) && $post->selection_type === 'breaking_news';
            });
        }

        // Apply sorting based on admin preference
        if ($sort === 'random') {
            // Randomize the array order
            shuffle($posts);
        } else {
            // Sort by creation date (newest first)
            $posts = sortArrayOfObjects($posts, 'created_at', 'desc');
        }

        // Apply limit and reset array keys
        return array_slice($posts, 0, $limit > 0 ? $limit : 10);
    }

    /**
     * Retrieves popular posts
     */
    public function getPopularPosts(int $langId): ?array
    {
        $limit = (int)($this->config->popular_posts_limit ?? 5);

        return $this->cacheService->get(
            sprintf('popular_posts_lang_%d_limit_%d', $langId, $limit),
            fn() => model('PostModel')->getPopularPosts($langId, $limit),
            'app_medium'
        );
    }

    /**
     * Extracts the top 3 most viewed posts from a given list
     */
    public function getTrendingPosts(array $sourceData): array
    {
        $flatList = [];

        foreach ($sourceData as $item) {
            if (is_array($item)) {
                $flatList = array_merge($flatList, $item);
            } elseif (is_object($item)) {
                $flatList[] = $item;
            }
        }

        if (empty($flatList)) {
            return [];
        }

        usort($flatList, fn($a, $b) => (int)$b->pageviews <=> (int)$a->pageviews);

        return array_slice($flatList, 0, 3);
    }

    /**
     * Retrieves limited RSS posts
     */
    public function getRssPosts(int $langId, string $type, int $id = 0, int $limit = 15): ?array
    {
        if (!in_array($type, ['latest', 'category', 'author'], true)) {
            return [];
        }

        $cacheKey = sprintf('rss_posts_lang_%d_%s%s_%d', $langId, $type, $id ? '_' . $id : '', $limit);

        return $this->cacheService->get(
            $cacheKey,
            fn() => match ($type) {
                'latest' => model('PostModel')->getLatestPosts($langId, $limit, 0, true)['posts'] ?? [],
                'category' => model('PostModel')->findAllByCategoryId($id, $limit, true),
                'author' => model('PostModel')->findAllByUserId($id, $langId, $limit, true),
                default => [],
            },
            'app_short'
        );
    }

    /**
     * Retrieves Google News Feed posts
     */
    public function getGoogleNewsFeedPosts(int $langId, ?int $categoryId = null, ?int $limit = 50): ?array
    {
        $cacheKey = sprintf('google_news_feeds_lang_%d_limit_%d%s', $langId, $limit, $categoryId !== null ? '_cat_' . $categoryId : '');

        return $this->cacheService->get(
            $cacheKey,
            fn() => model('PostModel')->getGoogleNewsFeedPosts($langId, $categoryId, $limit),
            'app_micro'
        );
    }

    /**
     * Retrieves Google News Sitemap posts
     */
    public function getGoogleNewsSitemapPosts(): ?array
    {
        return $this->cacheService->get(
            'google_news_sitemap',
            fn() => model('PostModel')->getGoogleNewsSitemapPosts(),
            'app_micro'
        );
    }

    /**
     * Increments the view count for a specific post
     */
    public function incrementPostViews(int $postId): bool
    {
        $request = service('request');
        $session = session();
        $pageViewsModel = model('PageViewsModel');
        $postModel = model('PostModel');

        // Retrieve viewed posts from session, fallback to empty array
        $viewedPosts = $session->get('viewed_posts');
        if (!is_array($viewedPosts)) {
            $viewedPosts = [];
        }

        // Prevent duplicate view tracking per session
        if (isset($viewedPosts[$postId])) {
            return false;
        }

        // Robot Check: Do not track views from search engines or bots
        $agent = $request->getUserAgent();
        if ($agent->isRobot()) {
            return false;
        }

        // GDPR-Compliant Hashing: Create a unique footprint without storing raw IPs
        $appKey = (string)model('ConfigModel')->getAppKey();
        $ipAddress = (string)($request->getIPAddress() ?? '');
        $userAgent = (string)($agent->getAgentString() ?? 'unknown_agent');
        $dateKey = date('Y-m-d');

        $visitHash = hash_hmac('sha256', $postId . $ipAddress . $userAgent . $dateKey, $appKey);

        // Database Duplicate Check: Verify if this exact hash already exists today
        if ($pageViewsModel->isDuplicateView($visitHash)) {
            // Sync Logic: DB has it, but Session lost it. Update session to prevent future DB hits.
            $viewedPosts[$postId] = time();

            // Inline session cleanup (Keep max 50 items to prevent session bloat)
            if (count($viewedPosts) > 50) {
                asort($viewedPosts);
                array_shift($viewedPosts);
            }
            $session->set('viewed_posts', $viewedPosts);
            return false;
        }

        // Retrieve required Post and User Data
        $post = $postModel->select('posts.id, posts.user_id, posts.lang_id, users.reward_system, users.balance')
            ->join('users', 'users.id = posts.user_id')
            ->where('posts.id', $postId)
            ->get()->getRow();

        if (empty($post)) {
            return false;
        }

        // Reward Calculation
        $rewardAmount = 0.00;
        if ((int)$this->config->reward_system_status === 1 && !empty($this->config->reward_amount)) {
            // Calculate reward for a single view (configured amount is per 1000 views)
            $rewardAmount = (float)$this->config->reward_amount / 1000;
        }

        $logData = [
            'post_id'       => $post->id,
            'post_user_id'  => $post->user_id,
            'lang_id'       => $post->lang_id,
            'reward_amount' => $rewardAmount,
            'visit_hash'    => $visitHash
        ];

        // Execute Database Transaction & Final Session Update
        if ($pageViewsModel->handleViewTransaction($post, $logData, $rewardAmount)) {
            $viewedPosts[$postId] = time();

            if (count($viewedPosts) > 50) {
                asort($viewedPosts);
                array_shift($viewedPosts);
            }

            $session->set('viewed_posts', $viewedPosts);
            return true;
        }

        return false;
    }

    /**
     * Prepares post list items for both insert and update operations
     */
    public function preparePostListItems(array $formData): array
    {
        $listItems = [];

        if (!empty($formData['items']) && is_array($formData['items'])) {
            foreach ($formData['items'] as $id => $arr) {

                // Strict safety: Skip if the nested item is not an array
                if (!is_array($arr)) {
                    continue;
                }

                $item = [
                    'item_order'        => isset($arr['item_order']) ? (int)$arr['item_order'] : 1,
                    'title'             => (string)($arr['title'] ?? ''),
                    'image_id'          => !empty($arr['image_id']) ? $arr['image_id'] : null,
                    'image_description' => (string)($arr['image_description'] ?? ''),
                    'content'           => (string)($arr['content'] ?? ''),
                    'parent_link_num'   => isset($arr['parent_link_num']) ? (int)$arr['parent_link_num'] : 0,
                ];

                // Append ID if it exists and is numeric (important for updates)
                if (is_numeric($id)) {
                    $item['id'] = (int)$id;
                }

                $listItems[] = $item;
            }
        }

        return $listItems;
    }

    /**
     * Handles related data operations (selections, tags, media) after the main post is saved
     */
    public function handlePostRelations(array $formData): object
    {
        // Set active toggle selections
        $activeSelections = [];
        $fields = ['is_slider', 'is_featured', 'is_breaking_news', 'is_recommended'];

        foreach ($fields as $fieldName) {
            if (isset($formData[$fieldName]) && (int)$formData[$fieldName] === 1) {
                $activeSelections[] = str_replace('is_', '', $fieldName);
            }
        }

        // Extract associated media IDs (CSV format)
        $imageIdsCsv = (string)($formData['additional_image_ids'] ?? '');
        $audioIdsCsv = (string)($formData['audio_ids'] ?? '');
        $attachmentIdsCsv = (string)($formData['attachment_ids'] ?? '');

        // Convert Tagify JSON string to a clean comma-separated string
        $tags = convertTagifyToString($formData['tags'] ?? '');

        // Return as a standard object for easy property access
        return (object)[
            'selections'       => $activeSelections,
            'imageIdsCsv'      => $imageIdsCsv,
            'audioIdsCsv'      => $audioIdsCsv,
            'attachmentIdsCsv' => $attachmentIdsCsv,
            'tags'             => $tags
        ];
    }

    /**
     * Handles the business logic for event registration
     */
    public function registerForEvent(int $postId, array $formData, ?int $userId = null): array
    {
        $post = getPostById($postId);
        if (empty($post) || empty($post->post_data)) {
            return ['status' => 0, 'message' => trans("invalid_attempt")];
        }

        $eventDetails = $post->post_data->details ?? null;

        // Ensure the event actually accepts internal registrations
        if (!$eventDetails || ($eventDetails->registration_type ?? 'none') !== 'internal') {
            return ['status' => 0, 'message' => trans("invalid_attempt")];
        }

        // Check login requirements if access is restricted
        $regAccess = $eventDetails->registration_access ?? 'public';
        if ($regAccess === 'registered' && !$userId) {
            return ['status' => 0, 'message' => trans("invalid_attempt")];
        }

        // Deadline Check
        $regDeadline = $eventDetails->registration_deadline ?? '';
        if (!empty($regDeadline)) {
            $nowTimestamp = timeNowObject()->getTimestamp();
            $timezone = $this->config->timezone ?? app_timezone();
            try {
                $deadlineTimestamp = \CodeIgniter\I18n\Time::parse($regDeadline, $timezone)->getTimestamp();
                if ($deadlineTimestamp < $nowTimestamp) {
                    return ['status' => 0, 'message' => trans("registration_closed")];
                }
            } catch (\Exception $e) {
            }
        }

        $eventRegModel = model('EventRegistrationModel');

        // Prevent Duplicate Registrations
        if ($eventRegModel->isRegistered($postId, $userId, $formData['email'])) {
            return ['status' => 0, 'message' => trans("event_already_registered_email")];
        }

        // Capacity Check
        $capacity = (int)($eventDetails->registration_capacity ?? 0);
        if ($capacity > 0) {
            $currentCount = $eventRegModel->getRegistrationCount($postId);
            if ($currentCount >= $capacity) {
                return ['status' => 0, 'message' => trans("registration_closed")];
            }
        }

        // Process Custom Data
        $customData = [];
        if (!empty($formData['custom_data']) && is_array($formData['custom_data'])) {
            foreach ($formData['custom_data'] as $key => $val) {
                $label = $formData['custom_labels'][$key] ?? $key;
                $customData[$label] = cleanStr($val);
            }
        }

        $successMessage = !empty($eventDetails->registration_success_message)
            ? $eventDetails->registration_success_message
            : trans("registration_successful");

        // Final Insertion
        $insertData = [
            'post_id'     => $postId,
            'user_id'     => $userId,
            'full_name'   => cleanStr($formData['full_name']),
            'email'       => cleanStr($formData['email']),
            'custom_data' => $customData,
        ];

        if ($eventRegModel->addRegistration($insertData)) {
            return ['status' => 1, 'message' => $successMessage];
        }

        return ['status' => 0, 'message' => trans("msg_error")];
    }

    /**
     * Exports event participants to a CSV file
     */
    public function exportEventParticipantsCsv(int $eventId): bool
    {
        $eventRegModel = model('EventRegistrationModel');
        $participants = $eventRegModel->where('post_id', $eventId)->orderBy('id', 'ASC')->findAll();

        if (empty($participants)) {
            setErrorMessage(trans("no_records_found"));
            return false;
        }

        // Prepare the Data Array
        $exportData = [];
        $count = 1;

        foreach ($participants as $item) {
            $additionalInfo = "";

            // Parse custom_data JSON safely
            if (!empty($item->custom_data)) {
                $cData = is_string($item->custom_data) ? json_decode(stripslashes($item->custom_data), true) : (array)$item->custom_data;

                if (is_array($cData)) {
                    $parts = [];
                    foreach ($cData as $key => $val) {
                        $parts[] = "$key: $val";
                    }
                    $additionalInfo = implode(' | ', $parts);
                }
            }

            // Map data to associative keys matching our column mapping
            $exportData[] = [
                'row_number'      => $count++,
                'full_name'       => $item->full_name,
                'email'           => $item->email,
                'user_type'       => !empty($item->user_id) ? trans("member") : trans("guest"),
                'additional_info' => $additionalInfo,
                'created_at'      => $item->created_at
            ];
        }

        // Define Column Mapping (Array Key => CSV Header)
        $columnMapping = [
            'row_number'      => '#',
            'full_name'       => trans('full_name'),
            'email'           => trans('email'),
            'user_type'       => 'User Type',
            'additional_info' => 'Additional Info',
            'created_at'      => trans('date')
        ];

        // Set Filename
        $filename = 'participants_event_' . $eventId . '_' . date('Y-m-d_H-i');

        // Trigger Export from Trait
        $this->streamArrayCsv($exportData, $columnMapping, $filename);

        return true;
    }
}