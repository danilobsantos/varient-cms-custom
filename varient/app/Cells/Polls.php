<?php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;

class Polls extends Cell
{
    protected ?object $widget = null;
    protected array $polls = [];
    protected string $pollsJson = '';

    /**
     * Mount the cell data
     */
    public function mount(object $widget, int $langId, ?int $userId = null): void
    {
        $this->widget = $widget;
        $this->polls = service('dataService')->getSidebarPolls($langId, $userId);
        $this->pollsJson = json_encode($this->polls);
    }

    /**
     * Render the cell view
     */
    public function render(): string
    {
        return view('themes/common/cells/polls', [
            'widget'    => $this->widget,
            'polls'     => $this->polls,
            'pollsJson' => $this->pollsJson
        ]);
    }
}