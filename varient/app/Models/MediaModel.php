<?php

namespace App\Models;

class MediaModel extends BaseModel
{
    protected $table = 'media';
    protected $allowedFields = ['media_type', 'file_name', 'file_path', 'storage', 'user_id', 'is_downloadable'];

    const ALLOWED_SELECTIONS = ['video', 'audio', 'attachment'];

    /**
     * Creates a new media file
     */
    public function create(array $data): ?int
    {
        if ($this->insert($data) === false) {
            return null;
        }

        return (int)$this->getInsertID();
    }

    /**
     * Syncs media associations for a post based on a specific selection type
     */
    public function syncMediaForPost(int $postId, ?string $mediaIdsCsv, string $selectionType): bool
    {
        if (!in_array($selectionType, self::ALLOWED_SELECTIONS)) {
            return false;
        }

        $newIds = [];
        if (!empty($mediaIdsCsv)) {
            $newIds = array_map('intval', explode(',', $mediaIdsCsv));
            $newIds = array_unique(array_filter($newIds));
        }

        $currentMedia = $this->db->table('post_media')->where('post_id', $postId)->where('selection_type', $selectionType)->get()->getResultArray();
        $currentIds = array_column($currentMedia, 'media_id');

        // Differences
        $idsToDelete = array_diff($currentIds, $newIds);
        $idsToInsert = array_diff($newIds, $currentIds);

        if (empty($idsToDelete) && empty($idsToInsert)) {
            return true;
        }

        $this->db->transStart();

        if (!empty($idsToDelete)) {
            $this->db->table('post_media')->where('post_id', $postId)->where('selection_type', $selectionType)->whereIn('media_id', $idsToDelete)->delete();
        }

        if (!empty($idsToInsert)) {
            $dataToInsert = [];
            foreach ($idsToInsert as $mediaId) {
                $dataToInsert[] = [
                    'post_id'        => $postId,
                    'media_id'       => $mediaId,
                    'selection_type' => $selectionType
                ];
            }
            $this->db->table('post_media')->insertBatch($dataToInsert);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Finds a media by Id
     */
    public function findById(int $id): object
    {
        return $this->select('media.*, post_media.id AS post_media_id, post_media.selection_type AS selection_type')
            ->join('post_media', 'post_media.media_id = media.id', 'INNER')
            ->where('media.id', $id)
            ->first();
    }

    /**
     * Finds all media files for a post filtered by selection type
     */
    public function findAllMediaByPostId(int $postId, string $selectionType): array
    {
        if (!in_array($selectionType, self::ALLOWED_SELECTIONS)) {
            return [];
        }

        if (empty($postId)) {
            return [];
        }

        return $this->select('media.*, post_media.id AS post_media_id')
            ->join('post_media', 'post_media.media_id = media.id', 'INNER')
            ->where('post_media.post_id', $postId)
            ->where('post_media.selection_type', $selectionType)
            ->orderBy('post_media.id', 'ASC')
            ->findAll();
    }

    /**
     * Finds and paginates media by type
     */
    public function findAllPaginated(int $page = 1, int $perPage = 18, ?string $mediaType = 'file', ?string $searchTerm = null): array
    {
        $builder = $this->builder();

        $builder->where('media_type', $mediaType);

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
     *  Deletes post medias
     */
    public function deletePostMedias(int $postId): bool
    {
        return $this->db->table('post_media')
            ->where('post_id', $postId)
            ->delete();
    }

    /**
     * Deletes a media record, its relationships, and the physical file safely
     */
    public function deleteMedia(int $id): bool
    {
        $media = $this->find($id);
        if (!$media) {
            return false;
        }

        // Check ownership
        if (!checkFileOwnership($media->user_id)) {
            return false;
        }

        $this->db->transStart();

        $this->db->table('post_media')->where('media_id', $media->id)->delete();

        $this->delete($id);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return false;
        }

        deleteStorageFile($media->file_path, $media->storage);

        return true;
    }
}