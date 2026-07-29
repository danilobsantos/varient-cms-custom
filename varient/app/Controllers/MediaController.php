<?php

namespace App\Controllers;

use App\Services\UploadService;

class MediaController extends BaseAdminController
{
    protected $filesPerPage;
    protected $postItemSources;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        checkPermission('add_post');

        $this->filesPerPage = defined('FILE_MANAGER_PER_PAGE') ? FILE_MANAGER_PER_PAGE : 120;

        $this->postItemSources = ['quiz_image', 'recipe_image', 'event_avatar_image', 'content_image'];
    }

    /**
     * Media
     */
    public function media()
    {
        $source = inputGet('source');

        if (!in_array($source, ['image', 'content_image', 'audio', 'video', 'file'])) {
            $source = 'image';
        }

        $allowedExtensions = getAllowedExtensionsBySource($source);

        return view('admin/media/media', [
            'title'             => trans("media"),
            'source'            => $source,
            'allowedExtensions' => $allowedExtensions
        ]);
    }

    /**
     * List
     */
    public function list()
    {
        $source = inputGet('source') ?? 'image';

        if ($source === 'image') {
            return $this->loadImages();
        } elseif (in_array($source, $this->postItemSources)) {
            return $this->loadItemImages($source);
        }

        return $this->loadMedia($source);
    }

    /**
     * Upload
     */
    public function upload()
    {
        $source = inputPost('source') ?? 'image';

        if ($source === 'image') {
            return $this->uploadImage();
        } elseif (in_array($source, $this->postItemSources)) {
            return $this->uploadItemImage($source);
        }

        return $this->uploadMedia($source);
    }

    /**
     * Update
     */
    public function update($id)
    {
        $source = inputPost('source');

        $data = [
            'file_name'       => inputPost('file_name'),
            'alt_text'        => inputPost('alt_text'),
            'is_downloadable' => (bool)inputPost('is_downloadable'),
        ];

        $success = false;
        if ($source === 'image') {
            $success = model('ImageModel')->update($id, $data);
        } else {
            $model = model('MediaModel');
            if (in_array($source, $this->postItemSources)) {
                $model = model('PostItemImageModel');
            }

            $file = $model->find($id);
            if (!empty($file)) {
                $currentFullName = $file->file_name;
                $extension = pathinfo($currentFullName, PATHINFO_EXTENSION);
                $newBaseName = $data['file_name'];

                if (!empty($extension)) {
                    $data['file_name'] = $newBaseName . '.' . $extension;
                } else {
                    $data['file_name'] = $newBaseName;
                }

                $success = $model->update($id, $data);
            }
        }

        if ($success) {
            return jsonResponse([
                'status' => 'success'
            ]);
        } else {
            return jsonResponse([
                'status' => 'error',
            ], 500);
        }
    }

    /**
     * Delete
     */
    public function delete()
    {
        $source = inputPost('source');
        $ids = inputPost('ids');

        if (empty($ids) || !is_array($ids)) {
            return jsonResponse([
                'status' => 'error',
            ], 400);
        }

        $model = null;
        $deleteMethod = '';

        if ($source === 'image') {
            $model = model('ImageModel');
            $deleteMethod = 'deleteImage';
        } elseif (in_array($source, $this->postItemSources)) {
            $model = model('PostItemImageModel');
            $deleteMethod = 'deleteImage';
        } else {
            $model = model('MediaModel');
            $deleteMethod = 'deleteMedia';
        }

        $allSucceeded = true;
        foreach ($ids as $id) {
            if (!$model->$deleteMethod((int)$id)) {
                $allSucceeded = false;
                break;
            }
        }

        if ($allSucceeded) {
            return jsonResponse([
                'status' => 'success'
            ]);
        } else {
            return jsonResponse([
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Load Images
     */
    private function loadImages()
    {
        $model = model('ImageModel');

        $page = (int)inputGet('page') ?: 1;
        $searchTerm = cleanStr(inputGet('search')) ?? null;

        $files = [];
        $images = $model->findAllPaginated($page, $this->filesPerPage, $searchTerm);
        $pager = $model->pager;

        if (!empty($images)) {
            foreach ($images as $image) {
                $url = getStorageFileUrl($image->image_mid, $image->storage);
                $urlOriginal = getStorageFileUrl($image->image_default, $image->storage);

                $files[] = [
                    'id'           => $image->id,
                    'title'        => $image->file_name,
                    'alt_text'     => $image->alt_text,
                    'file_url'     => $url,
                    'file_orj_url' => $urlOriginal,
                    'file_name'    => $image->file_name
                ];
            }
        }

        $hasMore = $pager->getCurrentPage() < $pager->getPageCount();
        $nextPage = $hasMore ? $pager->getCurrentPage() + 1 : null;

        return jsonResponse([
            'files'    => $files,
            'type'     => 'image',
            'hasMore'  => $hasMore,
            'nextPage' => $nextPage
        ]);
    }

    /**
     * Load Item Images
     */
    private function loadItemImages($source)
    {
        $model = model('PostItemImageModel');

        $page = (int)inputGet('page') ?: 1;
        $searchTerm = cleanStr(inputGet('search')) ?? null;

        $files = [];
        $images = $model->findAllPaginated((string)$source, $page, $this->filesPerPage, $searchTerm);
        $pager = $model->pager;

        if (!empty($images)) {
            foreach ($images as $image) {
                $imagePath = !empty($image->image_small) ? $image->image_small : $image->image_default;
                $url = getStorageFileUrl($imagePath, $image->storage);
                $urlOriginal = getStorageFileUrl($image->image_default, $image->storage);

                $files[] = [
                    'id'           => $image->id,
                    'title'        => $image->file_name ?? '',
                    'alt_text'     => '',
                    'file_url'     => $url,
                    'file_orj_url' => $urlOriginal,
                    'file_name'    => $image->file_name
                ];
            }
        }

        $hasMore = $pager->getCurrentPage() < $pager->getPageCount();
        $nextPage = $hasMore ? $pager->getCurrentPage() + 1 : null;

        return jsonResponse([
            'files'    => $files,
            'type'     => 'image',
            'hasMore'  => $hasMore,
            'nextPage' => $nextPage
        ]);
    }

    /**
     * Load Media
     */
    private function loadMedia($source)
    {
        $model = model('MediaModel');

        $mediaType = match ((string)$source) {
            'audio', 'video' => $source,
            default => 'file',
        };

        $page = (int)inputGet('page') ?: 1;
        $searchTerm = cleanStr(inputGet('search')) ?? null;

        $files = [];
        $media = $model->findAllPaginated($page, $this->filesPerPage, (string)$mediaType, $searchTerm);
        $pager = $model->pager;

        if (!empty($media)) {
            foreach ($media as $item) {
                $url = getStorageFileUrl($item->file_path, $item->storage);
                $titleWithoutExtension = pathinfo($item->file_name ?? 'file', PATHINFO_FILENAME);

                $files[] = [
                    'id'              => $item->id,
                    'title'           => $titleWithoutExtension,
                    'alt_text'        => '',
                    'file_url'        => $url,
                    'file_name'       => $item->file_name,
                    'is_downloadable' => $item->is_downloadable
                ];
            }
        }

        $hasMore = $pager->getCurrentPage() < $pager->getPageCount();
        $nextPage = $hasMore ? $pager->getCurrentPage() + 1 : null;

        return jsonResponse([
            'files'    => $files,
            'type'     => $mediaType,
            'hasMore'  => $hasMore,
            'nextPage' => $nextPage
        ]);
    }

    /**
     * Upload Image
     */
    public function uploadImage()
    {
        $model = model('ImageModel');
        $uploadService = new UploadService($this->config);

        $uploadedImage = $uploadService->uploadPostImage('file');

        if (!empty($uploadedImage)) {
            $image = $model->create($uploadedImage, (int)user()->id);
            if (!empty($image)) {
                $file = [
                    'id'       => $image->id,
                    'title'    => $image->file_name,
                    'alt_text' => '',
                    'file_url' => getStorageFileUrl($image->image_default ?? '', $image->storage)
                ];

                return jsonResponse([
                    'status' => 'success',
                    'file'   => $file
                ]);
            }
        }

        return jsonResponse([
            'status'  => 'error',
            'message' => 'File upload failed or database save error.'
        ], 400);
    }

    /**
     * Upload Item Image
     */
    public function uploadItemImage($source)
    {
        $model = model('PostItemImageModel');
        $uploadService = new UploadService($this->config);

        $uploadedImage = $uploadService->uploadPostItemImage('file', (string)$source);

        if (!empty($uploadedImage)) {
            $filename = $uploadedImage['originalName'] ?? '';
            $basename = pathinfo($filename, PATHINFO_FILENAME) ?: 'file';
            $extension = pathinfo($filename, PATHINFO_EXTENSION) ?: 'jpg';

            $sizes = ['default', 'small'];
            $imagePaths = [];
            foreach ($sizes as $size) {
                $key = "image_{$size}";
                $imagePaths[$key] = $uploadedImage[$key] ?? null;
            }

            // Merge image paths with other data
            $image = array_merge($imagePaths, [
                'file_name'      => $basename,
                'file_extension' => $extension,
                'storage'        => $uploadedImage['storage'] ?? $this->config->active_storage,
                'user_id'        => user()->id,
            ]);

            if ($imageId = $model->create($image, (string)$source)) {
                $fileUrl = getStorageFileUrl($image['image_small'] ?? '', $image['storage']);
                $file = [
                    'id'       => $imageId,
                    'title'    => $basename,
                    'alt_text' => '',
                    'file_url' => $fileUrl
                ];

                return jsonResponse([
                    'status' => 'success',
                    'file'   => $file
                ]);
            }
        }

        return jsonResponse([
            'status'  => 'error',
            'message' => 'File upload failed or database save error.'
        ], 400);
    }

    /**
     * Upload Media
     */
    private function uploadMedia($source)
    {
        $model = model('MediaModel');
        $uploadService = new UploadService($this->config);

        $source = strtolower((string)$source);
        $mediaKey = match ($source) {
            'audio', 'video' => $source,
            default => 'file',
        };

        $uploadedMedia = $uploadService->uploadMedia('file', $mediaKey);

        if (!empty($uploadedMedia)) {

            $media = [
                'media_type'      => $mediaKey,
                'file_name'       => $uploadedMedia['originalName'] ?? '',
                'file_path'       => $uploadedMedia['path'] ?? '',
                'storage'         => $uploadedMedia['storage'] ?? $this->config->active_storage,
                'user_id'         => user()->id,
                'is_downloadable' => 1
            ];

            if ($mediaId = $model->create($media)) {
                $fileUrl = getStorageFileUrl($media['file_path'] ?? '', $media['storage']);
                $titleWithoutExtension = pathinfo($media['file_name'], PATHINFO_FILENAME);

                $file = [
                    'id'              => $mediaId,
                    'title'           => $titleWithoutExtension,
                    'file_name'       => $media['file_name'],
                    'alt_text'        => '',
                    'file_url'        => $fileUrl,
                    'is_downloadable' => 1
                ];

                return jsonResponse([
                    'status' => 'success',
                    'file'   => $file
                ]);
            }
        }

        return jsonResponse([
            'status'  => 'error',
            'message' => 'File upload failed. UploadedMedia empty: ' . (empty($uploadedMedia) ? 'yes' : 'no')
        ], 400);
    }

}
