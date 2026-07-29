<?php

namespace App\Controllers;

use App\Services\RssService;

class RssController extends BaseAdminController
{
    protected $rssModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        checkPermission('rss_feeds');

        $this->rssModel = model('RssModel');
    }

    /**
     * RSS Feeds
     */
    public function rssFeeds()
    {
        $filters = [
            'lang_id' => (int)inputGet('lang_id'),
            'q'       => cleanStr(inputGet('q')),
        ];

        return view('admin/rss/feeds', [
            'title'         => trans("rss_feeds"),
            'panelSettings' => panelSettings(),
            'feeds'         => $this->rssModel->findAllPaginated($filters, $this->perPage),
            'pager'         => $this->rssModel->pager
        ]);
    }

    /**
     * Add Feed
     */
    public function addFeed()
    {
        if (isPostMethod()) {
            $postData = $this->request->getPost();

            $postData['user_id'] = user()->id;

            $feed = new \App\Entities\Rss();

            $feed->fill($postData);

            $feed->processForm($postData);

            if ($this->rssModel->trySave($feed)) {

                $feedId = (int)($this->rssModel->insertID() ?? 0);

                $newFeed = $this->rssModel->find($feedId);
                if (!empty($newFeed)) {
                    $rssService = new RssService();
                    $rssService->importFeed($newFeed);
                }

                setSuccessMessage("msg_added");
                return redirect()->to(getBackUrl());
            }

            return redirect()->to(getBackUrl())->with('errors', $this->rssModel->errors());
        }

        return view('admin/rss/form', [
            'title'          => trans("add_feed"),
            'action'         => adminUrl('rss-feeds/add'),
            'breadcrumbLink' => ['label' => trans("rss_feeds"), 'url' => adminUrl('rss-feeds')]
        ]);
    }

    /**
     * Edit Feed
     */
    public function editFeed($id)
    {
        $feed = $this->rssModel->find($id);
        if (!$feed) {
            setErrorMessage('msg_error');
            return redirect()->to(adminUrl('rss-feeds'));
        }

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            $feed->fill($postData);

            $feed->processForm($postData);

            if ($this->rssModel->trySave($feed)) {
                setSuccessMessage("msg_updated");
                return redirect()->to(getBackUrl());
            }

            return redirect()->to(getBackUrl())->with('errors', $this->rssModel->errors());
        }

        // Category data
        $categorySelectorData = model('CategoryModel')->selectorGetTree($feed->category_id, $feed->lang_id);

        return view('admin/rss/form', [
            'title'                => trans("update_feed"),
            'action'               => adminUrl('rss-feeds/edit/' . $feed->id),
            'feed'                 => $feed,
            'categorySelectorData' => json_encode($categorySelectorData),
            'breadcrumbLink'       => ['label' => trans("rss_feeds"), 'url' => adminUrl('rss-feeds')]
        ]);
    }

    /**
     * Import Feed Posts
     */
    public function importFeedPosts()
    {
        $feedId = (int)inputPost('feed_id');

        $feed = $this->rssModel->find($feedId);
        if (empty($feed)) {
            setErrorMessage("msg_error");
            return redirect()->to(adminUrl('rss-feeds'));
        }

        $rssService = new RssService();
        $rssService->importFeed($feed);

        setSuccessMessage("msg_updated");

        clearContentCache();

        return redirect()->to(adminUrl('rss-feeds'));
    }

    /**
     * AJAX Endpoint: Delete Feed
     *
     * @method POST
     */
    public function deleteFeed()
    {
        $id = (int)inputPost('id');

        $feed = $this->rssModel->find($id);
        if (empty($feed)) {
            return jsonResponse(false);
        }

        if ($this->rssModel->delete($id)) {
            setSuccessMessage("msg_deleted");
            return jsonResponse(true);
        }

        setErrorMessage("msg_error");
        return jsonResponse(false);
    }
}