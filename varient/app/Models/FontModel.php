<?php

namespace App\Models;

class FontModel extends BaseModel
{
    protected $table = 'fonts';
    protected $allowedFields = ['font_name', 'font_family', 'font_key', 'font_type', 'path_400', 'path_600', 'path_700', 'is_default'];
    protected $cacheGroup = 'app';

    protected $validationRules = [
        'font_name' => [
            'label' => 'trans:font_name',
            'rules' => 'required|max_length[255]',
        ]
    ];

    /**
     * Create a new font
     */
    public function create(array $data): bool
    {
        $data['font_key'] = strSlug($data['font_name'] ?? uniqid());

        $this->setFontFamily($data);

        return (bool)$this->insert($data);
    }

    /**
     * Update a font
     */
    public function updateFont(array $data): bool
    {
        $id = $data['id'] ?? null;
        if (empty($id)) {
            return false;
        }

        $this->setFontFamily($data);

        return (bool)$this->update($id, $data);
    }

    /**
     * Set font family
     */
    public function setFontFamily(array &$data): void
    {
        $fontName = $data['font_name'] ?? '';
        $fontName = str_replace(['"', "'"], '', $fontName);
        $fontName = trim($fontName);

        if (empty($fontName)) {
            return;
        }

        $fontType = trim($data['font_type'] ?? 'sans-serif');

        $fontFamily = '"' . $fontName . '", ' . $fontType;

        $data['font_family'] = $fontFamily;
    }

    /**
     * Finds selected fonts
     */
    public function findAllSelected(object $settings): array
    {
        $fontIds = array_filter([
            'primary'   => (int)($settings->primary_font ?? 0),
            'secondary' => (int)($settings->secondary_font ?? 0),
            'content'   => (int)($settings->content_font ?? 0),
        ]);

        if (empty($fontIds)) {
            return [];
        }

        $fonts = $this->whereIn('id', array_values($fontIds))->findAll();

        // Map fonts by ID for quick lookup
        $fontsById = [];
        foreach ($fonts as $font) {
            $fontsById[$font->id] = $font;
        }

        // Match fonts to their corresponding keys (primary, secondary, tertiary)
        $selectedFonts = [];
        foreach ($fontIds as $key => $id) {
            if (isset($fontsById[$id])) {
                $selectedFonts[$key] = $fontsById[$id];
            }
        }

        return $selectedFonts;
    }

    /**
     * Finds, filters, and paginates all languages
     */
    public function findAllPaginated(int $perPage): array
    {
        return $this->orderBy('id', 'DESC')
            ->paginate($perPage);
    }
}
