<?php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;

class TrendingPosts extends Cell
{
    protected array $trendingPosts = [];

    /**
     * Mount the cell data
     */
    public function mount(?array $latestCategoryPosts): void
    {
        $this->trendingPosts = service('postService')->getTrendingPosts($latestCategoryPosts);
    }

    /**
     * Render the cell view
     */
    public function render(): string
    {
        return view('themes/common/cells/trending_posts', [
            'trendingPosts' => $this->trendingPosts
        ]);
    }
}