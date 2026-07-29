<?php

namespace App\Models;

class ImageModel extends BaseModel
{
    protected $table = 'images';
    protected $allowedFields = ['image_big', 'image_default', 'image_slider', 'image_mid', 'image_small', 'file_extension', 'file_name', 'alt_text', 'storage', 'user_id'];
    protected $createdField = 'created_at';

    /**
     * Creates a new image
     */
    public function create(array $uploadedImage, int $userId): ?object
    {
        $filename = $uploadedImage['originalName'] ?? '';
        $extension = $uploadedImage['extension'] ?? 'jpg';
        $basename = pathinfo($filename, PATHINFO_FILENAME) ?: 'file';

        $sizes = ['big', 'default', 'slider', 'mid', 'small'];
        $imagePaths = [];
        foreach ($sizes as $size) {
            $key = "image_{$size}";
            $imagePaths[$key] = $uploadedImage[$key] ?? null;
        }

        $data = array_merge($imagePaths, [
            'file_name'      => $basename,
            'file_extension' => $extension,
            'storage'        => $uploadedImage['storage'] ?? $this->config->active_storage,
            'user_id'        => $userId
        ]);

        if ($this->insert($data) === false) {
            return null;
        }

        $insertId = $this->getInsertID();

        return $this->find($insertId);
    }

    /**
     * Finds, filters, and paginates all images
     */
    public function findAllPaginated(int $page = 1, int $perPage = 18, ?string $searchTerm = null): array
    {
        $builder = $this->select('id, image_default, image_mid, file_extension, file_name, storage, alt_text, user_id');

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
     * Syncs images for a post in the post_additional_images pivot table
     */
    public function syncImagesForPost(int $postId, ?string $imageIdsCsv): bool
    {
        $newIds = [];
        if (!empty($imageIdsCsv)) {
            $newIds = array_map('intval', explode(',', $imageIdsCsv));
            $newIds = array_unique(array_filter($newIds));
        }

        $currentImages = $this->db->table('post_additional_images')->where('post_id', $postId)->get()->getResultArray();
        $currentIds = array_column($currentImages, 'image_id');

        $idsToDelete = array_diff($currentIds, $newIds);
        $idsToInsert = array_diff($newIds, $currentIds);

        if (empty($idsToDelete) && empty($idsToInsert)) {
            return true;
        }

        $this->db->transStart();

        if (!empty($idsToDelete)) {
            $this->db->table('post_additional_images')->where('post_id', $postId)->whereIn('image_id', $idsToDelete)->delete();
        }

        if (!empty($idsToInsert)) {
            $dataToInsert = [];
            foreach ($idsToInsert as $imageId) {
                $dataToInsert[] = [
                    'post_id'  => $postId,
                    'image_id' => $imageId
                ];
            }

            $this->db->table('post_additional_images')->insertBatch($dataToInsert);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Finds all images associated with a specific post ID
     */
    public function findAllByPostId(int $postId): array
    {
        if (empty($postId)) {
            return [];
        }

        return $this->select('images.*, post_additional_images.id AS post_image_id')
            ->join('post_additional_images', 'post_additional_images.image_id = images.id', 'INNER')
            ->where('post_additional_images.post_id', $postId)
            ->orderBy('post_additional_images.id', 'ASC')
            ->findAll();
    }

    /**
     * Deletes post images
     */
    public function deletePostImages(int $postId): bool
    {
        if ($this->config->delete_images_with_post == 1) {

            $additionalImages = $this->findAllByPostId($postId);
            $imageIds = !empty($additionalImages) ? array_column($additionalImages, 'id') : [];

            $post = model('PostModel')->select('image_id')->find($postId);
            if (!empty($post) && !empty($post->image_id)) {
                $imageIds[] = $post->image_id;
            }

            $imageIds = array_unique(array_filter(array_map('intval', $imageIds)));

            if (!empty($imageIds)) {
                foreach ($imageIds as $id) {
                    $this->deleteImage($id);
                }
            }
        }

        return $this->db->table('post_additional_images')
            ->where('post_id', $postId)
            ->delete();
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
            $sizes = ['image_big', 'image_default', 'image_slider', 'image_mid', 'image_small'];

            foreach ($sizes as $size) {
                if (!empty($image->$size)) {
                    deleteStorageFile($image->$size, $image->storage);
                }
            }
            return true;
        }

        return false;
    }
}