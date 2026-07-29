<?php

namespace App\Controllers;

use App\Services\UploadService;

class GalleryController extends BaseAdminController
{
    protected $galleryImageModel;
    protected $galleryAlbumModel;
    protected $galleryCategoryModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        checkPermission('gallery');

        $this->galleryImageModel = model('GalleryImageModel');
        $this->galleryAlbumModel = model('GalleryAlbumModel');
        $this->galleryCategoryModel = model('GalleryCategoryModel');
    }

    /**
     * Albums
     */
    public function albums()
    {
        $filters = [
            'lang_id' => (int)inputGet('lang_id'),
            'q'       => cleanStr(inputGet('q')),
        ];

        return view('admin/gallery/albums', [
            'title'         => trans("albums"),
            'panelSettings' => panelSettings(),
            'albums'        => $this->galleryAlbumModel->findAllPaginated($filters, $this->perPage),
            'pager'         => $this->galleryAlbumModel->pager
        ]);
    }

    /**
     * Add Album
     */
    public function addAlbum()
    {
        if (isPostMethod()) {
            $postData = $this->request->getPost();

            if ($this->galleryAlbumModel->create($postData)) {
                setSuccessMessage("msg_added");
                return redirect()->to(adminUrl('gallery/albums/add'));
            } else {
                return redirect()->back()->withInput()->with('errors', $this->galleryAlbumModel->errors());
            }
        }

        return view('admin/gallery/form_album', [
            'title'          => trans("add_album"),
            'action'         => adminUrl('gallery/albums/add'),
            'breadcrumbLink' => ['label' => trans("albums"), 'url' => adminUrl('gallery/albums')]
        ]);
    }

    /**
     * Edit Album
     */
    public function editAlbum($id)
    {
        $album = $this->galleryAlbumModel->find($id);
        if (!$album) {
            setErrorMessage('msg_error');
            return redirect()->to(adminUrl('gallery/albums'));
        }

        if (isPostMethod()) {
            $postData = $this->request->getPost();
            if ($this->galleryAlbumModel->updateAlbum($album, $postData)) {
                setSuccessMessage("msg_updated");
                return redirect()->to(adminUrl('gallery/albums/edit/' . $id));
            } else {
                return redirect()->back()->withInput()->with('errors', $this->galleryAlbumModel->errors());
            }
        }

        return view('admin/gallery/form_album', [
            'title'          => trans("update_album"),
            'action'         => adminUrl('gallery/albums/edit/' . $id),
            'album'          => $album,
            'breadcrumbLink' => ['label' => trans("albums"), 'url' => adminUrl('gallery/albums')]
        ]);
    }

    /**
     * Delete Album
     */
    public function deleteAlbum()
    {
        $id = (int)inputPost('id');

        if ($this->galleryAlbumModel->hasCategories($id)) {
            setErrorMessage("msg_delete_album");
            return jsonResponse(false);
        }

        if ($this->galleryAlbumModel->delete($id)) {
            setSuccessMessage("msg_deleted");
        } else {
            setErrorMessage("msg_error");
        }

        return jsonResponse(true);
    }

    /**
     * Categories
     */
    public function categories()
    {
        $filters = [
            'lang_id' => (int)inputGet('lang_id'),
            'q'       => cleanStr(inputGet('q')),
        ];

        return view('admin/gallery/categories', [
            'title'         => trans("categories"),
            'panelSettings' => panelSettings(),
            'categories'    => $this->galleryCategoryModel->findAllPaginated($filters, $this->perPage),
            'pager'         => $this->galleryCategoryModel->pager
        ]);
    }

    /**
     * Add Category
     */
    public function addCategory()
    {
        if (isPostMethod()) {
            $postData = $this->request->getPost();
            if ($this->galleryCategoryModel->create($postData)) {
                setSuccessMessage("msg_added");
                return redirect()->to(adminUrl('gallery/categories/add'));
            } else {
                return redirect()->back()->withInput()->with('errors', $this->galleryCategoryModel->errors());
            }
        }

        return view('admin/gallery/form_category', [
            'title'          => trans("add_category"),
            'action'         => adminUrl('gallery/categories/add'),
            'albums'         => $this->galleryAlbumModel->findAllByLang($this->activeLang->id),
            'breadcrumbLink' => ['label' => trans("categories"), 'url' => adminUrl('gallery/categories')]
        ]);
    }

    /**
     * Edit Category
     */
    public function editCategory($id)
    {
        $category = $this->galleryCategoryModel->find($id);
        if (!$category) {
            setErrorMessage('msg_error');
            return redirect()->to(adminUrl('gallery/categories'));
        }

        if (isPostMethod()) {
            $postData = $this->request->getPost();
            if ($this->galleryCategoryModel->updateCategory($category, $postData)) {
                setSuccessMessage("msg_updated");
                return redirect()->to(adminUrl('gallery/categories/edit/' . $id));
            } else {
                return redirect()->back()->withInput()->with('errors', $this->galleryCategoryModel->errors());
            }
        }

        return view('admin/gallery/form_category', [
            'title'          => trans("update_category"),
            'action'         => adminUrl('gallery/categories/edit/' . $id),
            'category'       => $category,
            'albums'         => $this->galleryAlbumModel->findAllByLang($category->lang_id),
            'breadcrumbLink' => ['label' => trans("categories"), 'url' => adminUrl('gallery/categories')]
        ]);
    }

    /**
     * Delete Category
     */
    public function deleteCategory()
    {
        $id = (int)inputPost('id');
        if ($this->galleryCategoryModel->hasImages($id)) {
            setErrorMessage("msg_delete_images");
            return jsonResponse(false);
        }

        if ($this->galleryCategoryModel->delete($id)) {
            setSuccessMessage("msg_deleted");
        } else {
            setErrorMessage("msg_error");
        }

        return jsonResponse(true);
    }

    /**
     * Images
     */
    public function images()
    {
        $filters = [
            'lang_id'     => (int)inputGet('lang_id'),
            'album_id'    => (int)inputGet('album_id'),
            'category_id' => (int)inputGet('category_id'),
            'q'           => cleanStr(inputGet('q')),
        ];

        return view('admin/gallery/images', [
            'title'         => trans("images"),
            'panelSettings' => panelSettings(),
            'images'        => $this->galleryImageModel->findAllPaginated($filters, $this->perPage),
            'pager'         => $this->galleryImageModel->pager,
            'albums'        => !empty($filters['lang_id']) ? $this->galleryAlbumModel->findAllByLang($filters['lang_id']) : null,
            'categories'    => !empty($filters['album_id']) ? $this->galleryCategoryModel->findAllByAlbum($filters['album_id']) : null
        ]);
    }

    /**
     * Add Image
     */
    public function addImage()
    {
        if (isPostMethod()) {
            $postData = $this->request->getPost();

            $uploadedFiles = $postData['uploaded_files'] ?? [];

            foreach ($uploadedFiles as $jsonFile) {
                $fileData = json_decode($jsonFile, true);

                if (empty($fileData['path_big'])) {
                    continue;
                }

                $image = new \App\Entities\GalleryImage();
                $image->fill($postData);

                $image->processImage($fileData);

                $this->galleryImageModel->trySave($image);
            }

            setSuccessMessage("msg_added");
            return redirect()->to(adminUrl('gallery/images/add'));
        }

        return view('admin/gallery/form_image', [
            'title'          => trans("add_image"),
            'action'         => adminUrl('gallery/images/add'),
            'albums'         => $this->galleryAlbumModel->findAllByLang($this->activeLang->id),
            'breadcrumbLink' => ['label' => trans("images"), 'url' => adminUrl('gallery/images')]
        ]);
    }

    /**
     * Edit Image
     */
    public function editImage($id)
    {
        $image = $this->galleryImageModel->find($id);
        if (!$image) {
            setErrorMessage('msg_error');
            return redirect()->to(adminUrl('gallery/images'));
        }

        if (isPostMethod()) {
            $oldImage = clone $image;

            $postData = $this->request->getPost();
            $image->fill($postData);

            $uploadService = new UploadService($this->config);
            $uploadedFile = $uploadService->uploadGalleryImage('file');

            $image->processImage($uploadedFile);

            if ($this->galleryImageModel->trySave($image)) {
                if (!empty($uploadedFile) && !empty($uploadedFile['path_big'])) {
                    if ($oldImage->path_big !== $image->path_big) {
                        deleteStorageFile($oldImage->path_big, $oldImage->storage);
                        deleteStorageFile($oldImage->path_small, $oldImage->storage);
                    }
                }

                setSuccessMessage("msg_updated");
                return redirect()->to(getBackUrl());
            } else {
                return redirect()->back()->withInput()->with('errors', $this->galleryImageModel->errors());
            }
        }

        return view('admin/gallery/form_image', [
            'title'          => trans("update_image"),
            'action'         => adminUrl('gallery/images/edit/' . $id),
            'image'          => $image,
            'albums'         => $this->galleryAlbumModel->findAllByLang($image->lang_id),
            'categories'     => $this->galleryCategoryModel->findAllByAlbum($image->album_id),
            'breadcrumbLink' => ['label' => trans("images"), 'url' => adminUrl('gallery/images')]
        ]);
    }

    /**
     * AJAX Endpoint: Delete image
     *
     * @method POST
     */
    public function deleteImage()
    {
        $id = (int)inputPost('id');
        $ids = inputPost('ids');

        if (!empty($id)) {
            $ids = [$id];
        }

        if (empty($ids) || !is_array($ids)) {
            return jsonResponse(false);
        }

        $deletedCount = 0;

        foreach ($ids as $itemId) {
            $itemId = (int)$itemId;
            $image = $this->galleryImageModel->find($itemId);

            if (!empty($image)) {
                if ($this->galleryImageModel->delete($itemId)) {

                    deleteStorageFile($image->path_big, $image->storage);
                    deleteStorageFile($image->path_small, $image->storage);

                    $deletedCount++;
                }
            }
        }

        if ($deletedCount > 0) {
            setSuccessMessage("msg_deleted");
            return jsonResponse(true);
        }

        setErrorMessage("msg_error");
        return jsonResponse(false);
    }

    /**
     * AJAX Endpoint: Upload image
     *
     * @method POST
     */
    public function uploadImage()
    {
        $uploadService = new UploadService($this->config);
        $uploadedFile = $uploadService->uploadGalleryImage('file');

        if (!empty($uploadedFile)) {
            return jsonResponse([
                'success' => true,
                'paths'   => [
                    'path_big'   => $uploadedFile['path_big'] ?? '',
                    'path_small' => $uploadedFile['path_small'] ?? '',
                    'storage'    => $uploadedFile['storage'] ?? '',
                ]
            ]);
        }

        return jsonResponse([
            'success' => false
        ]);
    }

    /**
     * AJAX Endpoint: Get albums by language
     *
     * @method POST
     */
    public function getAlbumsByLang()
    {
        $langId = (int)inputPost('lang_id');

        $albums = $this->galleryAlbumModel->findAllByLang($langId);

        $data = [];
        if (!empty($albums)) {
            foreach ($albums as $item) {
                $data[] = [
                    'id'   => (int)$item->id,
                    'name' => $item->name,
                ];
            }
        }

        return jsonResponse([
            'result' => 1,
            'albums' => $data,
        ]);
    }

    /**
     * AJAX Endpoint: Get categories by album
     *
     * @method POST
     */
    public function getCategoriesByAlbum()
    {
        $albumId = (int)inputPost('album_id');

        $categories = $this->galleryCategoryModel->findAllByAlbum($albumId);

        $data = [];
        if (!empty($categories)) {
            foreach ($categories as $item) {
                $data[] = [
                    'id'   => (int)$item->id,
                    'name' => $item->name,
                ];
            }
        }

        return jsonResponse([
            'result'     => 1,
            'categories' => $data,
        ]);
    }

    /**
     * AJAX Endpoint: Set image as album cover
     *
     * @method POST
     */
    public function setImageAsAlbumCover()
    {
        $id = (int)inputPost('id');

        $this->galleryImageModel->setAsAlbumCover($id);

        return jsonResponse(true);
    }
}
