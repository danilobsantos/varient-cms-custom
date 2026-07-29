<?php

namespace App\Controllers;

class CategoryController extends BaseAdminController
{
    protected $categoryModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->categoryModel = model('CategoryModel');
    }

    /**
     * Categories
     */
    public function categories()
    {
        checkPermission('categories');

        $parentId = (int)inputGet('parent_id');
        $langId = (int)inputGet('lang_id') ?: $this->activeLang->id;
        $search = cleanStr(inputGet('q'));

        $result = $this->categoryModel->getCategories($parentId, $langId, $search, $this->perPage);

        // Data packet for view
        $data = [
            'categories'   => isset($result['data']) ? $result['data'] : $result, // Pagination control
            'pager'        => isset($result['pager']) ? $result['pager'] : null,
            'isSearchMode' => !empty($search)
        ];

        // If AJAX request
        if ($this->request->isAJAX()) {
            return view('admin/category/_list', $data);
        }

        $data['title'] = trans("categories");

        return view('admin/category/categories', $data);
    }

    /**
     * Add Category
     */
    public function addCategory()
    {
        checkPermission('categories');

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            $category = new \App\Entities\Category();
            $category->fill($postData);

            // Fetch parent if exists
            $parent = null;
            if (!empty($category->parent_id)) {
                $parent = $this->categoryModel->find($category->parent_id);
            }

            $category->handleCategoryForm($postData, $parent);

            // Convert the processed Entity back to a plain Array
            $insertData = $category->toArray();

            // Manually trigger Model Validation before database operations
            if (!$this->categoryModel->validate($insertData)) {
                return redirect()->back()->withInput()->with('errors', $this->categoryModel->errors());
            }

            if ($this->categoryModel->create($insertData)) {
                clearContentCache();
                setSuccessMessage("msg_added");
                return redirect()->to(adminUrl('categories/add'));
            } else {
                setErrorMessage(trans("msg_error"));
                return redirect()->back()->withInput();
            }
        }

        return view('admin/category/form', [
            'title'          => trans("add_category"),
            'action'         => adminUrl('categories/add'),
            'panelSettings'  => panelSettings(),
            'breadcrumbLink' => ['label' => trans("categories"), 'url' => adminUrl('categories')]
        ]);
    }

    /**
     * Edit Category
     */
    public function editCategory($id)
    {
        checkPermission('categories');

        $category = $this->categoryModel->find($id);

        if (!$category) {
            setErrorMessage('msg_error');
            return redirect()->to(adminUrl('categories'));
        }

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            $category->fill($postData);

            // Fetch parent if exists
            $parent = null;
            if (!empty($category->parent_id)) {
                $parent = $this->categoryModel->find($category->parent_id);
            }

            $category->handleCategoryForm($postData, $parent);

            // Convert the processed Entity back to a plain Array
            $updateData = $category->toArray();

            // Manually trigger Model Validation before database operations
            if (!$this->categoryModel->validate($updateData)) {
                return redirect()->back()->withInput()->with('errors', $this->categoryModel->errors());
            }

            if ($this->categoryModel->updateCategory($id, $updateData)) {

                // Updates the premium status of all descendant categories
                if ((int)($postData['apply_to_subcategories'] ?? 0) == 1) {

                    $exclusivePrice = $postData['exclusive_price'] ?? null;
                    if (!empty($exclusivePrice)) {
                        $this->exclusive_price = numToDecimal($exclusivePrice);
                    }

                    $dataPremium = [
                        'is_premium'      => (int)($postData['is_premium'] ?? 0),
                        'is_exclusive'    => (int)($postData['is_exclusive'] ?? 0),
                        'exclusive_price' => $exclusivePrice,
                    ];

                    $this->categoryModel->updateDescendantsPremiumStatus($category->id, $dataPremium);
                }

                clearContentCache();
                setSuccessMessage("msg_updated");
                return redirect()->to(adminUrl('categories/edit/' . $id));
            } else {
                setErrorMessage(trans("msg_error"));
                return redirect()->back()->withInput();
            }
        }

        $selectorData = $this->categoryModel->selectorGetTree($category->id, $category->lang_id, $category->id);

        return view('admin/category/form', [
            'title'          => trans("update_category"),
            'action'         => adminUrl('categories/edit/' . $category->id),
            'category'       => $category,
            'selectorData'   => json_encode($selectorData),
            'widgets'        => model('WidgetModel')->findAllOrdered(),
            'breadcrumbLink' => ['label' => trans("categories"), 'url' => adminUrl('categories')]
        ]);
    }

    /**
     * AJAX Endpoint: Category Selector List
     *
     * @method POST
     */
    public function selectorListCategories()
    {
        $parentId = (int)inputGet('parent_id');
        $langId = (int)inputGet('lang_id') ?: $this->activeLang->id;
        $excludeId = (int)inputGet('exclude_id');

        $data = $this->categoryModel->selectorList($parentId, $langId, $excludeId);

        return jsonResponse($data);
    }

    /**
     * AJAX Endpoint: Category Selector Search
     *
     * @method POST
     */
    public function selectorSearchCategories()
    {
        $q = inputGet('q');
        $langId = (int)inputGet('lang_id') ?: $this->activeLang->id;
        $excludeId = (int)inputGet('exclude_id');

        $data = $this->categoryModel->selectorSearch($q, $langId, $excludeId);

        return jsonResponse($data);
    }

    /**
     * AJAX Endpoint: Populate categories dropdown
     *
     * @method POST
     */
    public function populateCategoriesDropdown()
    {
        $q = cleanStr(inputPost('q'));

        $categories = $this->categoryModel->findAllByName($q);

        return jsonResponse(['items' => $categories]);
    }

    /**
     * Delete Category
     */
    public function deleteCategory()
    {
        if (!hasPermission('categories')) {
            return jsonResponse(false);
        }

        $id = (int)inputPost('id');

        if (!empty($this->categoryModel->findAllByParentId($id))) {
            setErrorMessage("msg_delete_subcategories");
            return jsonResponse(false);
        }

        if ($this->categoryModel->hasPosts($id)) {
            setErrorMessage("msg_delete_posts");
            return jsonResponse(false);
        }

        if ($this->categoryModel->delete($id)) {
            clearContentCache();
            setSuccessMessage("msg_deleted");
        } else {
            setErrorMessage("msg_error");
        }

        return jsonResponse(true);
    }

    /**
     * Tags
     */
    public function tags()
    {
        checkPermission('tags');

        $tagModel = model('TagModel');

        if (isPostMethod()) {
            $postData = $this->request->getPost();
            $tagId = $postData['id'] ?? null;

            $tag = !empty($tagId) ? $tagModel->find($tagId) : new \App\Entities\Tag();
            $tag->fill($postData);
            $tag->processForm($postData);

            $existing = $tagModel->findBySlug($tag->tag_slug, (int)$tag->lang_id);
            if (!empty($existing)) {
                if (empty($tagId) || $existing->id != $tagId) {
                    setErrorMessage('msg_tag_exists');
                    return redirect()->to(getBackUrl());
                }
            }

            $tagModel->trySave($tag) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

            return redirect()->to(getBackUrl());
        }

        $filters = [
            'lang_id' => (int)inputGet('lang_id'),
            'q'       => cleanStr(inputGet('q')),
        ];

        $data = [
            'title'         => trans("tags"),
            'panelSettings' => panelSettings(),
            'tags'          => $tagModel->findAllPaginated($filters, $this->perPage),
            'pager'         => $tagModel->pager
        ];

        return view('admin/tag/tags', $data);
    }

    /**
     * AJAX Endpoint: Delete Tag
     *
     * @method POST
     */
    public function deleteTag()
    {
        checkPermission('tags');

        $ids = inputPost('ids');

        $ids = is_array($ids) ? $ids : [$ids];
        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            return jsonResponse(false);
        }

        model("TagModel")->deleteTags($ids) ? setSuccessMessage("msg_deleted") : setErrorMessage("msg_error");

        return jsonResponse(true);
    }
}
