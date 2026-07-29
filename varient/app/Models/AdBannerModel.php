<?php

namespace App\Models;

class AdBannerModel extends BaseModel
{
    protected $table = 'ad_banners';
    protected $allowedFields = [
        'ad_space_id',
        'banner_url_desktop',
        'banner_path_desktop',
        'banner_storage_desktop',
        'ad_code_desktop',
        'banner_url_mobile',
        'banner_path_mobile',
        'banner_storage_mobile',
        'ad_code_mobile',
        'status',
        'expiry_date'
    ];

    public function __construct()
    {
        parent::__construct();

        // Auto-create ad_banners table if not exists for easy deployment
        $this->db->query("CREATE TABLE IF NOT EXISTS `ad_banners` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `ad_space_id` int(11) NOT NULL,
            `banner_url_desktop` varchar(1000) DEFAULT NULL,
            `banner_path_desktop` varchar(1000) DEFAULT NULL,
            `banner_storage_desktop` varchar(50) DEFAULT NULL,
            `ad_code_desktop` text DEFAULT NULL,
            `banner_url_mobile` varchar(1000) DEFAULT NULL,
            `banner_path_mobile` varchar(1000) DEFAULT NULL,
            `banner_storage_mobile` varchar(50) DEFAULT NULL,
            `ad_code_mobile` text DEFAULT NULL,
            `status` tinyint(4) NOT NULL DEFAULT 1,
            `expiry_date` datetime DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `ad_space_id` (`ad_space_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
    }

    /**
     * Retrieves active and non-expired banners for a given ad space
     */
    public function getActiveBanners(int $adSpaceId): array
    {
        $now = date('Y-m-d H:i:s');
        return $this->where('ad_space_id', $adSpaceId)
            ->where('status', 1)
            ->groupStart()
                ->where('expiry_date', null)
                ->orWhere('expiry_date >', $now)
            ->groupEnd()
            ->findAll();
    }
}
