<?php

namespace App\Models;

class AdSpaceModel extends BaseModel
{
    protected $table = 'ad_spaces';
    protected $allowedFields = ['lang_id', 'ad_space', 'ad_code_desktop', 'desktop_width', 'desktop_height', 'ad_code_mobile', 'mobile_width', 'mobile_height', 'display_category_id', 'paragraph_number'];
    protected $cacheGroup = 'app';

    /**
     * Creates a new ad space
     */
    public function create(?int $langId, ?string $key): ?int
    {
        if (empty($langId) || empty($key)) {
            return null;
        }

        $cleanKey = cleanStr($key);

        $data = [
            'lang_id'          => $langId,
            'ad_space'         => $cleanKey,
            'ad_code_desktop'  => '',
            'desktop_width'    => 728,
            'desktop_height'   => 90,
            'ad_code_mobile'   => '',
            'mobile_width'     => 300,
            'mobile_height'    => 250,
            'paragraph_number' => 1
        ];

        if ($cleanKey == 'sidebar_1' || $cleanKey == 'sidebar_2') {
            $data['desktop_width'] = 336;
            $data['desktop_height'] = 280;
        }

        if ($this->insert($data) === false) {
            return null;
        }

        return (int)$this->insertID();
    }

    /**
     * Updates an ad space
     */
    public function updateAdSpace(array $data): bool
    {
        if (empty($data['id'])) {
            return false;
        }

        if (empty($data['paragraph_number'])) {
            $data['paragraph_number'] = 1;
        }

        return $this->update($data['id'], $data);
    }

    /**
     * Finds ad spaces by language
     */
    public function findAllByLangId(int $langId): array
    {
        $adSpaces = $this->where('lang_id', $langId)
            ->orderBy('id', 'DESC')
            ->findAll();

        $bannerModel = model('AdBannerModel');

        foreach ($adSpaces as $space) {
            $banners = $bannerModel->getActiveBanners($space->id);

            // Automatic Migration & Cleanup Check
            $hasOldData = (trim($space->ad_code_desktop ?? '') !== '' || trim($space->ad_code_mobile ?? '') !== '');
            if ($hasOldData) {
                $totalBanners = $bannerModel->where('ad_space_id', $space->id)->countAllResults();
                if ($totalBanners === 0) {
                    $bannerModel->insert([
                        'ad_space_id'     => $space->id,
                        'ad_code_desktop' => $space->ad_code_desktop,
                        'ad_code_mobile'  => $space->ad_code_mobile,
                        'status'          => 1,
                        'expiry_date'     => null
                    ]);
                    $banners = $bannerModel->getActiveBanners($space->id);
                }

                // Clear the old columns in ad_spaces to prevent re-migration or fallback rendering
                $this->update($space->id, [
                    'ad_code_desktop' => '',
                    'ad_code_mobile'  => ''
                ]);
                $space->ad_code_desktop = '';
                $space->ad_code_mobile = '';
            }

            $space->banners = $banners;
        }

        return $adSpaces;
    }

    /**
     * Finds an ad space by key
     */
    public function findByKey(int $langId, string $key): ?object
    {
        $cleanKey = cleanStr($key);

        if (empty($langId) || empty($cleanKey)) {
            return null;
        }

        $row = $this->where('lang_id', $langId)->where('ad_space', $cleanKey)->first();
        if (!empty($row)) {
            return $row;
        }

        $rowId = $this->create($langId, $key);
        if (empty($rowId)) {
            return null;
        }

        return $this->find($rowId);
    }
}