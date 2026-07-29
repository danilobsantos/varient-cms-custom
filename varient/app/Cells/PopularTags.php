<?php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;

class PopularTags extends Cell
{
    protected array $tags = [];

    /**
     * Mount the cell data
     */
    public function mount(int $langId): void
    {
        $this->tags = service('dataService')->getPopularTags($langId);
    }

    /**
     * Render the cell view
     */
    public function render(): string
    {
        return view('themes/common/cells/popular_tags', [
            'tags' => $this->tags
        ]);
    }
}