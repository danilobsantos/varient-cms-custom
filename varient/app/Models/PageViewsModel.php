<?php

namespace App\Models;

class PageViewsModel extends BaseModel
{
    protected $table = 'post_pageviews_month';
    protected $allowedFields = ['post_id', 'post_user_id', 'lang_id', 'reward_amount', 'visit_hash', 'created_at'];
    protected $createdField = 'created_at';

    /**
     * Checks if the visit hash already exists in the database
     */
    public function isDuplicateView(string $visitHash): bool
    {
        $result = $this->select('id')
            ->where('visit_hash', cleanStr($visitHash))
            ->first();

        return !empty($result);
    }

    /**
     * Executes the view counting process within a database transaction
     */
    public function handleViewTransaction($post, array $logData, float $rewardAmount): bool
    {
        $this->db->transStart();

        $this->db->table('posts')
            ->where('id', (int)$post->id)
            ->set('pageviews', 'pageviews + 1', false)
            ->update();

        $this->insert($logData);

        if ($rewardAmount > 0) {

            $rewardAmount = (float)number_format($rewardAmount, 10, '.', '');

            $this->db->table('users')
                ->where('id', $post->user_id)
                ->set('balance', "ROUND(COALESCE(balance, 0) + {$rewardAmount}, 10)", false)
                ->set('total_pageviews', 'COALESCE(total_pageviews, 0) + 1', false)
                ->update();
        }

        $this->db->transComplete();

        $status = (bool)$this->db->transStatus();
        if ($status) {
            try {
                $cacheService = service('cacheService');
                $cacheService->delete('dashboard_heavy_stats_admin', 'app_micro');
                $cacheService->delete('dashboard_heavy_stats_superadmin', 'app_micro');
                if (!empty($post->user_id)) {
                    $cacheService->delete('dashboard_heavy_stats_user_' . (int)$post->user_id, 'app_micro');
                }
            } catch (\Throwable $e) {
            }
        }

        return $status;
    }

    /**
     * Gets page views counts by user ID
     */
    public function getUserPageViewsCountByDate($userId)
    {
        return $this->select('COUNT(id) AS count, SUM(reward_amount) as total_amount, DATE(created_at) AS date')
            ->where('post_user_id', $userId)
            ->where('reward_amount > 0')
            ->where('MONTH(created_at)', date('m'))
            ->groupBy('date')
            ->get()->getResult();
    }

    /**
     * Retrieves daily statistics for the current month
     */
    public function getMonthlyStats(?int $userId = null): array
    {
        $startDate = date('Y-m-01 00:00:00');
        $endDate = date('Y-m-t 23:59:59');

        $this->select('COUNT(*) AS count, SUM(reward_amount) AS total_amount, DATE(created_at) AS date', false)
            ->where('created_at >=', $startDate)
            ->where('created_at <=', $endDate);

        if ($userId !== null) {
            $this->where('post_user_id', $userId);
        }

        return $this->groupBy('DATE(created_at)', false)
            ->orderBy('date', 'ASC')
            ->get()->getResult();
    }

    /**
     * Retrieves formatted daily statistics for the current month
     */
    public function getMonthlyStatsForChart(?int $userId = null): object
    {
        $rawStats = $this->getMonthlyStats($userId);

        $dailyStats = [];
        foreach ($rawStats as $item) {
            // Instead of using strtotime() and date() which invoke the heavy timezone/datetime parser,
            // extract the day directly from the 'YYYY-MM-DD' string
            $dayNumber = (int)substr($item->date, 8, 2);
            $dailyStats[$dayNumber] = $item;
        }

        $labels = [];
        $views = [];
        $earnings = [];
        $totalViews = 0;
        $totalEarnings = 0.0;

        $numberOfDays = (int)date('t');
        $today = (int)date('j');

        $currentMonth = date('m'); // Kept as string for direct concatenation
        $currentYear = date('Y');

        for ($i = 1; $i <= $numberOfDays; $i++) {
            // mktime() inside a loop is expensive because it checks timezone and DST rules on every iteration
            $labels[] = sprintf('%s-%s-%02d', $currentYear, $currentMonth, $i);

            if ($i <= $today) {
                // Process past and present days
                if (isset($dailyStats[$i])) {
                    // Extract values
                    $dayView = (int)$dailyStats[$i]->count;
                    $dayEarning = (float)$dailyStats[$i]->total_amount;

                    // Push to chart arrays
                    $views[] = $dayView;
                    $earnings[] = number_format($dayEarning, 5, '.', '');

                    // Calculate Totals (Accumulate)
                    $totalViews += $dayView;
                    $totalEarnings += $dayEarning;
                } else {
                    // No data for this specific day (past or present)
                    $views[] = 0;
                    $earnings[] = 0;
                }
            } else {
                // Future days (Push null so the chart line stops seamlessly)
                $views[] = null;
                $earnings[] = null;
            }
        }

        return (object)[
            'labels'          => $labels,
            'views'           => $views,
            'earnings'        => $earnings,
            'total_pageviews' => $totalViews,
            'total_earnings'  => $totalEarnings
        ];
    }

    /**
     * Finds, filters, and paginates all page views
     */
    public function findAllPaginated(array $filters, int $perPage): array
    {
        $this->join('users', 'users.id = post_pageviews_month.post_user_id')
            ->select('post_pageviews_month.*, users.username AS author_username, users.slug AS author_slug');

        if (!empty($filters['q'])) {
            $q = cleanStr($filters['q']);
            $this->like('users.username', $q);
        }

        return $this->orderBy('id', 'DESC')
            ->paginate($perPage);
    }

    /**
     * Deletes records older than 30 days
     */
    public function deleteOldRecords(): int
    {
        $cutOffDate = date('Y-m-d H:i:s', strtotime('-30 days'));

        $this->db->table('post_pageviews_month')
            ->where('created_at <', $cutOffDate)
            ->limit(10000)
            ->delete();

        return $this->db->affectedRows();
    }
}