<?php

namespace App\Models;

class PostItemImageModel extends BaseModel
{
    protected $table = 'post_item_images';
    protected $allowedFields = ['item_type', 'image_default', 'image_small', 'file_name', 'file_extension', 'storage', 'user_id'];

    /**
     * Creates a new image
     */
    public function create(array $data, string $source): ?int
    {
        $data['item_type'] = $this->getItemTypeFromSource($source);

        if ($this->insert($data) === false) {
            return null;
        }

        return (int)$this->getInsertID();
    }

    /**
     * Finds records by specific IDs
     */
    public function findByIds(string $source, array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $itemType = $this->getItemTypeFromSource($source);

        return $this->select('*')
            ->where('item_type', $itemType)
            ->whereIn('id', $ids)
            ->orderBy('id', 'DESC')->findAll();
    }

    /**
     * Finds, filters, and paginates all images
     */
    public function findAllPaginated(string $source, int $page = 1, int $perPage = 18, ?string $searchTerm = null): array
    {
        $itemType = $this->getItemTypeFromSource($source);

        $builder = $this->select('*');

        if ($source !== 'content_image') {
            $builder->where('item_type', $itemType);
        }

        // Filter results by user
        if (!hasPermission('manage_all_posts') && (int)$this->config->file_manager_show_all_files !== 1) {
            $builder->where('user_id', user()->id);
        }

        if (!empty($searchTerm)) {
            $builder->groupStart()->orLike('file_name', $searchTerm)->groupEnd();
        }

        $builder->orderBy('id', 'DESC');

        return $this->paginate($perPage, 'default', $page);
    }

    /**
     * Deletes an image and its associated files from the server
     */
    public function deleteImage(int $id): bool
    {
        $image = $this->find($id);
        if (!$image) {
            return false;
        }

        // Check ownership
        if (!checkFileOwnership($image->user_id)) {
            return false;
        }

        if ($this->delete($id)) {

            $sizes = ['image_default', 'image_small'];

            foreach ($sizes as $size) {
                if (!empty($image->$size)) {
                    deleteStorageFile($image->$size, $image->storage);
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Determines the item type based on the source identifier
     */
    private function getItemTypeFromSource(string $source): string
    {
        return match ($source) {
            'recipe_image' => 'recipe',
            'quiz_image' => 'quiz',
            'event_avatar_image' => 'event_avatar',
            default => '',
        };
    }
}