<?php

namespace App\Models;

use App\Entities\Widget;

class WidgetModel extends BaseModel
{
    protected $table = 'widgets';
    protected $allowedFields = ['lang_id', 'title', 'content', 'widget_type', 'widget_order', 'status', 'is_custom', 'display_category_id', 'created_at'];
    protected $returnType = Widget::class;
    protected $createdField = 'created_at';
    protected $cacheGroup = 'app';

    protected $validationRules = [
        'title' => [
            'label' => 'trans:title',
            'rules' => 'required|max_length[500]'
        ]
    ];

    /**
     * Finds all widgets ordered by widget_order
     */
    public function findAllOrdered(): array
    {
        return $this->orderBy('widget_order')
            ->findAll();
    }

    /**
     * Findsall widgets by language
     */
    public function findAllByLang(int $langId): array
    {
        return $this->where('lang_id', $langId)
            ->orderBy('widget_order', 'ASC')
            ->findAll();
    }

    /**
     * Finds, filters, and paginates all widgets
     */
    public function findAllPaginated(int $perPage, array $filters = []): array
    {
        $builder = $this->select('widgets.*, c.name as category_name')
            ->join('categories c', 'c.id = widgets.display_category_id', 'left');

        if (!empty($filters['lang_id'])) {
            $builder->where('widgets.lang_id', (int)$filters['lang_id']);
        }
        if (isset($filters['status'])) {
            $status = $filters['status'] == 'inactive' ? 0 : 1;
            $builder->where('widgets.status', $status);
        }
        if (!empty($filters['q'])) {
            $builder->like('widgets.title', cleanStr($filters['q']));
        }

        return $builder->orderBy('widgets.id DESC')
            ->paginate($perPage);
    }

    /**
     * Adds default widgets
     */
    public function addDefaultWidgets(int $langId): void
    {
        $widgets = [
            ['Popular Posts', 'popular-posts'],
            ['Follow Us', 'follow-us'],
            ['Recommended Posts', 'recommended-posts'],
            ['Popular Tags', 'tags'],
            ['Voting Poll', 'poll']
        ];

        foreach ($widgets as $index => [$title, $type]) {
            $this->insert([
                'lang_id'      => $langId,
                'title'        => $title,
                'content'      => '',
                'widget_type'  => $type,
                'widget_order' => $index + 1,
                'status'       => 1,
                'is_custom'    => 0
            ]);
        }
    }
}