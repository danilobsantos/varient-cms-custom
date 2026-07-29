<?php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;

class PopularPosts extends Cell
{
    protected array $popularPosts = [];

    /**
     * Mount the cell data
     */
    public function mount(int $langId): void
    {
        $this->popularPosts = service('postService')->getPopularPosts($langId);
    }

    /**
     * Render the cell view
     */
    public function render(): string
    {
        return view('themes/common/cells/popular_posts', [
            'popularPosts' => $this->popularPosts
        ]);
    }
}