<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Data Service (The "Orchestra Conductor" / Repository)
 *
 * This is the main data access layer for the application.
 * It holds all business logic for retrieving cached data.
 * It coordinates both the CacheService and the Models to return data.
 *
 * Models remain "pure" and only handle database queries.
 * This service is the only place that knows *how* to cache model data.
 *
 * This class is instantiated and injected by Config/Services.php.
 */
class DataService
{
    /**
     * The application's cache service instance.
     */
    protected CacheService $cacheService;

    /**
     * Injects the dependencies (passed from Config/Services.php).
     *
     * @param CacheService $cacheService The shared instance of CacheService.
     */
    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Retrieves dashboard statistics, automatically caching them based on user role
     */
    public function getDashboardStats(): array
    {
        $user = user();
        $userId = (int)$user->id;
        $isAdmin = isAdmin();

        $cacheKey = $isAdmin ? 'dashboard_heavy_stats_admin' : 'dashboard_heavy_stats_user_' . $userId;

        $cachedStats = $this->cacheService->get(
            $cacheKey,
            function () use ($userId, $isAdmin) {

                $pageViewModel = model('PageViewsModel');

                $stats = [
                    'monthlyStatsForChart' => $pageViewModel->getMonthlyStatsForChart($isAdmin ? null : $userId),
                ];

                if ($isAdmin) {
                    $userModel = model('UserModel');
                    $commentModel = model('CommentModel');
                    $commentStats = $commentModel->getCommentStatistics();

                    // Upcoming Events
                    $upcomingEvents = model('PostModel')->getUpcomingActiveEvents(5);

                    $stats += [
                        'userCount'            => $userModel->countAllResults(),
                        'latestUsers'          => $userModel->orderBy('id', 'DESC')->findAll(8),
                        'newsletterEmailCount' => model('NewsletterModel')->countAllResults(),
                        'commentCount'         => (int)($commentStats['total'] ?? 0),
                        'approvedCommentCount' => (int)($commentStats['approved'] ?? 0),
                        'latestComments'       => $commentModel->findLatestCommentsByStatus(1, 5),
                        'upcomingEvents'       => $upcomingEvents,
                    ];
                }

                return $stats;
            },
            'app_micro'
        );

        $postModel = model('PostModel');
        $payoutModel = model('PayoutModel');

        $liveStats = [
            'dashboardPostStats' => $postModel->getDashboardStats($isAdmin ? null : $userId),
        ];

        if ($isAdmin) {
            $commentModel = model('CommentModel');
            $contactModel = model('ContactModel');
            $transactionModel = model('TransactionModel');

            $liveStats += [
                'pendingCommentCount'   => $commentModel->where('status', 0)->countAllResults(),
                'latestPendingComments' => $commentModel->findLatestCommentsByStatus(0, 10),
                'latestPendingPosts'    => $postModel->getLatestPendingPosts(10),
                'pendingPayoutCount'    => $payoutModel->where('status', 0)->countAllResults(),
                'contactMessagesCount'  => $contactModel->countAllResults(),
                'latestContactMessages' => $contactModel->orderBy('id', 'DESC')->findAll(5),
                'latestTransactions'    => $transactionModel->getLatestTransactions(),
            ];
        } else {
            $liveStats += [
                'pendingPayoutCount' => $payoutModel->where('status', 0)->where('user_id', $userId)->countAllResults(),
            ];
        }

        return array_merge($cachedStats, $liveStats);
    }

    /**
     * Retrieves fonts by settings
     */
    public function getFonts(object $settings): array
    {
        return $this->cacheService->get(
            'fonts_' . $settings->id,
            fn() => model('FontModel')->findAllSelected($settings),
            'app_long'
        );
    }

    /**
     * Retrieves widgets by language ID
     */
    public function getWidgets(int $langId): array
    {
        return $this->cacheService->get(
            'widgets_lang_' . $langId,
            fn() => model('WidgetModel')->findAllByLang($langId),
            'app_long'
        );
    }

    /**
     * Retrieves ad spaces by language ID
     */
    public function getAdSpaces(int $langId): array
    {
        return $this->cacheService->get(
            'ad_spaces_lang_' . $langId,
            fn() => model('AdSpaceModel')->findAllByLangId($langId),
            'app_long'
        );
    }

    /**
     * Retrieves menu links by language ID
     */
    public function getMenuLinks(int $langId, bool $buildTree): array
    {
        return $this->cacheService->get(
            'menu_links_lang_' . $langId,
            fn() => model('PageModel')->getMenuLinks($langId, $buildTree),
            'app_long'
        );
    }

    /**
     * Retrieves root categories
     */
    public function getRootCategoriesByLangId(int $langId = 0, ?bool $active = null): array
    {
        $activeKey = $active === null ? 'all' : (int)$active;

        return $this->cacheService->get(
            sprintf('root_categories_lang_%d_active_%s', $langId, $activeKey),
            fn() => model('CategoryModel')->findAllRoots($langId, $active),
            'app_long'
        );
    }

    /**
     * Retrieves category tree (Depth = 1)
     */
    public function getCategoryTree(int $langId): ?array
    {
        return $this->cacheService->get(
            sprintf('tree_lang_%d', $langId),
            fn() => model('CategoryModel')->findAllRootsWithChildren($langId, true),
            'app_long'
        );
    }

    /**
     * Retrieves a map of RootCategoryID => [SubCategoryIDs]
     */
    public function getSubCategoryIdsMap(int $langId, array $categoryTree): ?array
    {
        return $this->cacheService->get(
            'subcategory_ids_map_lang_' . $langId,
            fn() => model('CategoryModel')->getSubCategoryIdsMap($categoryTree),
            'app_long'
        );
    }

    /**
     * Retrieves popular tags
     */
    public function getPopularTags(int $langId)
    {
        $limit = defined('SIDEBAR_TAGS_LIMIT') ? (int)(SIDEBAR_TAGS_LIMIT) : 20;

        return $this->cacheService->get(
            sprintf('popular_tags_lang_%d_limit_%d', $langId, $limit),
            fn() => model('TagModel')->getPopularTags($langId, $limit),
            'app_medium'
        );
    }

    /**
     * Retrieves sidebar polls
     */
    public function getSidebarPolls(int $langId, ?int $userId = null): array
    {
        $pollsWithOptions = $this->cacheService->get(
            'polls_lang_' . $langId,
            fn() => model('PollModel')->getPollsWithOptions($langId),
            'app_long'
        );

        if (empty($pollsWithOptions)) return [];

        $widgetData = [];
        $userVotes = [];

        if ($userId) {
            $pollIds = array_column($pollsWithOptions, 'id');
            $userVotes = model('PollVoteModel')->getVotesForPolls($pollIds, $userId);
        }

        $isGuest = empty($userId);

        foreach ($pollsWithOptions as $poll) {
            $pollId = $poll['id'];

            // No DB query here! Just check our local $userVotes array
            $userVotedOptionId = $userVotes[$pollId] ?? 0;
            $hasVoted = ($userVotedOptionId > 0);

            $requireLogin = ($poll['vote_permission'] === 'registered' && $isGuest);

            $widgetData[] = [
                'id'                => $pollId,
                'question'          => $poll['question'],
                'hasVoted'          => $hasVoted,
                'userVotedOptionId' => $userVotedOptionId,
                'requireLogin'      => $requireLogin,
                'options'           => $poll['options']
            ];
        }

        return $widgetData;
    }
}