<?php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;

class RecommendedPosts extends Cell
{
    protected array $recommendedPosts = [];
    protected bool $highlightFirst = false;

    /**
     * Mount the cell data
     */
    public function mount(array $showcasePosts, ?bool $highlightFirst = false): void
    {
        // Fetch recommended posts using the injected showcase data
        $this->recommendedPosts = service('postService')->getRecommendedPosts($showcasePosts);

        $this->highlightFirst = $highlightFirst;
    }

    /**
     * Render the cell view
     */
    public function render(): string
    {
        // Do not render anything if there are no posts to display
        if (empty($this->recommendedPosts)) {
            return '';
        }

        return view('themes/common/cells/recommended_posts', [
            'recommendedPosts' => $this->recommendedPosts,
            'highlightFirst'   => $this->highlightFirst
        ]);
    }
}