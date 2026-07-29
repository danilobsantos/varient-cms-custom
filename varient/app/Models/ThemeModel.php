<?php

namespace App\Models;

class ThemeModel extends BaseModel
{
    protected $table = 'themes';
    protected $allowedFields = ['theme_mode', 'theme_color', 'block_color', 'mega_menu_color', 'is_active'];
    protected $cacheGroup = 'app';

    protected $validationRules = [
        'theme_mode'      => [
            'label' => 'trans:theme_mode',
            'rules' => 'required|in_list[light,dark]'
        ],
        'theme_color'     => [
            'label' => 'trans:theme_color',
            'rules' => 'required'
        ],
        'block_color'     => [
            'label' => 'trans:block_color',
            'rules' => 'required'
        ],
        'mega_menu_color' => [
            'label' => 'trans:mega_menu_color',
            'rules' => 'required'
        ],
    ];

    /**
     * Sets active theme
     */
    public function setTheme(array $data): bool
    {
        if (empty($data['id'])) return false;

        $id = $data['id'];

        if (!$this->find($id)) {
            return false;
        }

        $this->db->transStart();

        $this->builder()->update(['is_active' => 0]);

        $this->update($id, ['is_active' => 1]);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Updates a theme
     */
    public function updateTheme(array $data): bool
    {
        if (empty($data['id'])) return false;

        $theme = $this->find($data['id']);

        if (empty($theme)) return false;

        return $this->update($theme->id, $data);
    }
}