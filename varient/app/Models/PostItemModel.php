<?php

namespace App\Models;

class PostItemModel extends BaseModel
{
    protected $table = 'post_items';
    protected $allowedFields = ['post_id', 'title', 'content', 'image_id', 'image_description', 'item_order', 'parent_link_num'];

    /**
     * Synchronizes post items (Insert, Update, Delete)
     */
    public function syncItems(int $postId, array $items): bool
    {
        // Get all existing item IDs for this post from the database
        $existingItems = $this->select('id')->where('post_id', $postId)->findAll();
        $existingIds = !empty($existingItems) ? array_column($existingItems, 'id') : [];

        // Lists to hold categorized data
        $idsToKeep = [];
        $dataToUpdate = [];
        $dataToInsert = [];

        // Process the incoming items array
        foreach ($items as $item) {

            $item['post_id'] = $postId;

            if (isset($item['id']) && is_numeric($item['id'])) {
                $idsToKeep[] = $item['id'];
                $dataToUpdate[] = $item;
            } else {
                unset($item['id']);
                $dataToInsert[] = $item;
            }
        }

        // Calculate IDs to Delete
        $idsToDelete = array_diff($existingIds, $idsToKeep);

        $this->db->transStart();

        // DELETE removed items
        if (!empty($idsToDelete)) {
            $this->where('post_id', $postId)->whereIn('id', $idsToDelete)->delete();
        }

        // UPDATE existing items
        if (!empty($dataToUpdate)) {
            $this->updateBatch($dataToUpdate, 'id');
        }

        // INSERT new items
        if (!empty($dataToInsert)) {
            $this->insertBatch($dataToInsert);
        }

        $this->db->transComplete();

        return (bool)$this->db->transStatus();
    }

    /**
     * Finds all items associated with a specific post ID
     */
    public function findAllByPostId(int $postId, string $postFormat): array
    {
        if (!$postId) {
            return [];
        }

        $subQuery = $this->builder()->select('*')->where('post_id', $postId);

        if ($postFormat === 'recipe') {
            $joinTable = 'post_item_images img';
            $selectImages = 'img.image_default, img.image_small, img.storage';
        } else {
            $joinTable = 'images img';
            $selectImages = 'img.image_default, img.image_big, img.image_mid, img.image_small, img.storage';
        }

        return $this->db->newQuery()
            ->fromSubquery($subQuery, 'pi')
            ->select('pi.*')
            ->select($selectImages)
            ->join($joinTable, 'img.id = pi.image_id', 'left')
            ->orderBy('pi.item_order', 'ASC')
            ->get()->getResult();
    }

    /**
     * Find list item by page number
     */
    public function findByPageNumber(int $postId, int $pageNum = 1): ?object
    {
        $offset = max(0, $pageNum - 1);

        return $this->select('post_items.*, img.image_default, img.image_big, img.image_mid, img.image_small, img.storage')
            ->join('images img', 'img.id = post_items.image_id', 'left')
            ->where('post_id', $postId)
            ->orderBy('item_order IS NULL, item_order', 'ASC', false)
            ->orderBy('post_items.id', 'ASC')
            ->limit(1, $offset)
            ->first();
    }
}