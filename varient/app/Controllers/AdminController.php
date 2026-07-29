<?php

namespace App\Controllers;

use App\Services\DatabaseBackupService;
use App\Services\EmailService;
use App\Services\SitemapService;
use App\Services\NewsletterService;
use App\Services\UploadService;

class AdminController extends BaseAdminController
{
    protected $configModel;
    protected $settingsModel;
    protected $pageModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->configModel = model('ConfigModel');
        $this->settingsModel = model('SettingsModel');
        $this->pageModel = model('PageModel');
    }

    /**
     * Index Page
     */
    public function index()
    {
        checkPermission('admin_panel');

        $stats = service('dataService')->getDashboardStats();

        return view('admin/index', [
                'title'         => trans("index"),
                'isIndexPage'   => true,
                'today'         => date('d'),
                'numberOfDays'  => date('t') ?: 30,
                'panelSettings' => panelSettings(),
            ] + $stats);
    }

    /**
     * Themes
     */
    public function themes()
    {
        if (!isSuperAdmin()) {
            return redirect()->to(adminUrl());
        }

        $themeModel = model('ThemeModel');

        if (isPostMethod()) {
            $postData = $this->request->getPost();
            if ($postData['submit'] == 'theme') {
                $result = $themeModel->setTheme($postData);
            } elseif ($postData['submit'] == 'settings') {
                $result = $themeModel->updateTheme($postData);
            }

            if ($result) {
                setSuccessMessage("msg_updated");
                return redirect()->to(getBackUrl());
            } else {
                return redirect()->back()->withInput()->with('errors', $themeModel->errors());
            }
        }

        return view('admin/themes', [
            'title'  => trans("themes"),
            'themes' => $themeModel->findAll()
        ]);
    }

    /**
     * Navigation
     */
    public function navigation()
    {
        checkPermission('navigation');

        $selectedLangId = inputGet("lang");
        if (empty($selectedLangId)) {
            $selectedLangId = $this->activeLang->id;
            return redirect()->to(adminUrl('navigation?lang=' . $selectedLangId));
        }

        $data = [
            'title'          => trans("navigation"),
            'panelSettings'  => panelSettings(),
            'selectedLangId' => $selectedLangId,
            'menuLinks'      => $this->pageModel->getMenuLinks($selectedLangId, true)
        ];

        return view('admin/navigation/navigation', $data);
    }

    /**
     * Add Menu Link
     */
    public function addMenuLink()
    {
        checkPermission('navigation');

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            $page = new \App\Entities\Page();
            $page->fill($postData);

            $page->processForm($postData, 'link');

            if ($this->pageModel->trySave($page)) {
                setSuccessMessage("msg_added");
                return redirect()->to(adminUrl('navigation/add'));
            } else {
                return redirect()->back()->withInput()->with('errors', $this->pageModel->errors());
            }
        }

        $data = [
            'title'          => trans("add_menu_link"),
            'action'         => adminUrl('navigation/add'),
            'menuLinks'      => $this->pageModel->getMenuLinks($this->activeLang->id, true),
            'breadcrumbLink' => ['label' => trans("navigation"), 'url' => adminUrl('navigation')]
        ];

        return view('admin/navigation/form', $data);
    }

    /**
     * Edit Menu Link
     */
    public function editMenuLink($id)
    {
        checkPermission('navigation');

        $page = $this->pageModel->find($id);

        if (!$page) {
            setErrorMessage('msg_error');
            return redirect()->to(adminUrl('navigation'));
        }

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            $page->fill($postData);

            $page->processForm($postData, 'link');

            if ($this->pageModel->trySave($page)) {
                setSuccessMessage("msg_updated");
                return redirect()->to(getBackUrl());
            } else {
                return redirect()->back()->withInput()->with('errors', $this->pageModel->errors());
            }
        }

        $data = [
            'title'          => trans("update_link"),
            'action'         => adminUrl('navigation/edit/' . $page->id),
            'page'           => $page,
            'menuLinks'      => $this->pageModel->getMenuLinks($page->lang_id, true),
            'breadcrumbLink' => ['label' => trans("navigation"), 'url' => adminUrl('navigation')]
        ];

        return view('admin/navigation/form', $data);
    }

    /**
     * Update Navitation Settings Post
     */
    public function navigationSettingsPost()
    {
        checkPermission('navigation');

        $config = $this->configModel->first();

        $postData = $this->request->getPost();

        $config->fill($postData);

        $this->configModel->trySave($config) ? setSuccessMessage("msg_updated") : setErrorMessage("msg_error");

        return redirect()->to(getBackUrl());
    }

    /**
     * Pages
     */
    public function pages()
    {
        checkPermission('pages');

        $filters = [
            'lang_id' => (int)inputGet('lang_id'),
            'status'  => inputGet('status'),
            'q'       => cleanStr(inputGet('q')),
        ];

        $data = [
            'title'         => trans("pages"),
            'panelSettings' => panelSettings(),
            'pages'         => $this->pageModel->findAllPaginated($filters, $this->perPage),
            'pager'         => $this->pageModel->pager
        ];

        return view('admin/page/pages', $data);
    }

    /**
     * Add Page
     */
    public function addPage()
    {
        checkPermission('pages');

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            $page = new \App\Entities\Page();
            $page->fill($postData);

            $page->processForm($postData, 'page');

            if ($this->pageModel->trySave($page)) {

                $newPageId = $this->pageModel->getInsertID();

                $this->pageModel->updateSlug('posts', $newPageId);

                setSuccessMessage("msg_added");
                return redirect()->to(getBackUrl());
            } else {
                return redirect()->back()->withInput()->with('errors', $this->pageModel->errors());
            }
        }

        $data = [
            'title'                    => trans("add_page"),
            'action'                   => adminUrl('pages/add'),
            'menuLinks'                => $this->pageModel->getMenuLinks($this->activeLang->id, true),
            'breadcrumbLink'           => ['label' => trans("pages"), 'url' => adminUrl('pages')],
            'breadcrumbGenerateAiLink' => $this->aiWriter->status ? true : false
        ];

        return view('admin/page/form', $data);
    }

    /**
     * Edit Page
     */
    public function editPage($id)
    {
        checkPermission('pages');

        $page = $this->pageModel->find($id);

        if (!$page) {
            setErrorMessage('msg_error');
            return redirect()->to(adminUrl('pages'));
        }

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            $page->fill($postData);

            $page->processForm($postData, 'page');

            if ($this->pageModel->trySave($page)) {

                $this->pageModel->updateSlug('posts', $page->id);

                setSuccessMessage("msg_updated");
                return redirect()->to(getBackUrl());
            } else {
                return redirect()->back()->withInput()->with('errors', $this->pageModel->errors());
            }
        }

        $data = [
            'title'                    => trans("update_page"),
            'action'                   => adminUrl('pages/edit/' . $page->id),
            'page'                     => $page,
            'menuLinks'                => $this->pageModel->getMenuLinks($page->lang_id, true),
            'breadcrumbLink'           => ['label' => trans("pages"), 'url' => adminUrl('pages')],
            'breadcrumbGenerateAiLink' => $this->aiWriter->status ? true : false
        ];

        return view('admin/page/form', $data);
    }

    /**
     * AJAX Endpoint: Delete Page
     *
     * @method POST
     */
    public function deletePage()
    {
        checkPermission('pages');

        if (!checkPermission('pages') && !hasPermission('navigation')) {
            return jsonResponse(false);
        }

        $id = (int)inputPost('id');
        $page = $this->pageModel->find($id);
        if (!$page) {
            setErrorMessage('msg_error');
            return jsonResponse(false);
        }

        if (!$page->is_custom) {
            setErrorMessage("msg_page_delete");
            return jsonResponse(false);
        }

        if (!empty($this->pageModel->findAllByParentId($id))) {
            setErrorMessage("msg_delete_subpages");
            return jsonResponse(false);
        }

        if ($this->pageModel->delete($id)) {
            setSuccessMessage("msg_deleted");
        } else {
            setErrorMessage("msg_error");
        }

        return jsonResponse(true);
    }

    /**
     * AJAX Endpoint: Get menu links by language
     *
     * @method POST
     */
    public function getMenuLinksByLang()
    {
        $langId = (int)inputPost('lang_id');

        $html = '';

        if ($langId > 0) {
            $menuLinks = $this->pageModel->getMenuLinks($langId);

            if (!empty($menuLinks)) {
                foreach ($menuLinks as $item) {

                    if ($item->type !== 'category' && $item->location === 'main' && (int)$item->parent_id === 0) {
                        $html .= '<option value="' . (int)$item->id . '">' . esc($item->title) . '</option>';
                    }

                }
            }
        }

        return jsonResponse([
            'status' => 1,
            'html'   => $html,
        ]);
    }

    /**
     * Widgets
     */
    public function widgets()
    {
        checkPermission('widgets');

        $widgetModel = model('WidgetModel');

        $filters = $this->request->getGet(['lang_id', 'status', 'q']);

        return view('admin/widget/widgets', [
            'title'                       => trans("widgets"),
            'panelSettings'               => panelSettings(),
            'widgets'                     => $widgetModel->findAllPaginated($this->perPage, $filters),
            'pager'                       => $widgetModel->pager,
            'latestPostsTranslationArray' => model('LanguageTranslationModel')->findAllByLabel('latest_posts')
        ]);
    }

    /**
     * Add Widget
     */
    public function addWidget()
    {
        checkPermission('widgets');

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            $widgetModel = model('WidgetModel');

            $widget = new \App\Entities\Widget();
            $widget->fill($postData);

            if ($widgetModel->trySave($widget)) {
                setSuccessMessage("msg_added");
                return redirect()->to(getBackUrl());
            } else {
                return redirect()->back()->withInput()->with('errors', $widgetModel->errors());
            }
        }

        return view('admin/widget/form', [
            'title'          => trans("add_widget"),
            'action'         => adminUrl('widgets/add'),
            'categories'     => model('CategoryModel')->findAllRoots(null, true),
            'breadcrumbLink' => ['label' => trans("widgets"), 'url' => adminUrl('widgets')]
        ]);
    }

    /**
     * Edit Widget
     */
    public function editWidget($id)
    {
        checkPermission('widgets');

        $widgetModel = model('WidgetModel');

        $widget = $widgetModel->find($id);
        if (!$widget) {
            setErrorMessage('msg_error');
            return redirect()->to(adminUrl('widgets'));
        }

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            $widget->fill($postData);

            if ($widgetModel->trySave($widget)) {
                setSuccessMessage("msg_updated");
                return redirect()->to(getBackUrl());
            } else {
                return redirect()->back()->withInput()->with('errors', $widgetModel->errors());
            }
        }
        
        return view('admin/widget/form', [
            'title'          => trans("update_widget"),
            'action'         => adminUrl('widgets/edit/' . $widget->id),
            'widget'         => $widget,
            'categories'     => model('CategoryModel')->findAllRoots(null, true),
            'breadcrumbLink' => ['label' => trans("widgets"), 'url' => adminUrl('widgets')]
        ]);
    }

    /**
     * AJAX Endpoint: Delete Widget
     *
     * @method POST
     */
    public function deleteWidget()
    {
        checkPermission('widgets');

        $widgetModel = model('WidgetModel');

        $id = (int)inputPost('id');

        $widget = $widgetModel->find($id);
        if (!$widget) {
            setErrorMessage('msg_error');
            return jsonResponse(false);
        }

        if (!$widget->is_custom) {
            $lang = model('LanguageModel')->find($widget->lang_id);
            if (!empty($lang)) {
                setErrorMessage("msg_widget_delete");
                return jsonResponse(false);
            }
        }

        if ($widgetModel->delete($widget->id)) {
            setSuccessMessage("msg_deleted");
        } else {
            setErrorMessage("msg_error");
        }

        return jsonResponse(true);
    }

    /**
     * Polls
     */
    public function polls()
    {
        checkPermission('polls');

        $pollModel = model('PollModel');

        $filters = $this->request->getGet(['lang_id', 'status', 'q']);

        return view('admin/poll/polls', [
            'title'         => trans("polls"),
            'panelSettings' => panelSettings(),
            'polls'         => $pollModel->findAllPaginated($filters, $this->perPage),
            'pager'         => $pollModel->pager
        ]);
    }

    /**
     * Add Poll
     */
    public function addPoll()
    {
        checkPermission('polls');

        if (isPostMethod()) {
            $pollModel = model('PollModel');

            $postData = $this->request->getPost();

            if ($pollModel->create($postData)) {
                setSuccessMessage("msg_added");
                return redirect()->to(getBackUrl());
            } else {
                return redirect()->back()->withInput()->with('errors', $pollModel->errors());
            }
        }

        return view('admin/poll/form', [
            'title'          => trans("add_poll"),
            'action'         => adminUrl('polls/add'),
            'options'        => [(object)['id' => '', 'option_text' => '']],
            'breadcrumbLink' => ['label' => trans("polls"), 'url' => adminUrl('polls')]
        ]);
    }

    /**
     * Edit Poll
     */
    public function editPoll($id)
    {
        checkPermission('polls');

        $pollModel = model('PollModel');

        $poll = $pollModel->find($id);
        if (!$poll) {
            setErrorMessage('msg_error');
            return redirect()->to(adminUrl('polls'));
        }

        $action = adminUrl('polls/edit/' . $poll->id);

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            if ($pollModel->updatePoll($poll, $postData)) {
                setSuccessMessage("msg_updated");
                return redirect()->to($action);
            } else {
                return redirect()->back()->withInput()->with('errors', $pollModel->errors());
            }
        }

        $pollOptionModel = model('PollOptionModel');
        $pollOptions = $pollOptionModel->findAllByPollId($poll->id);

        $options = old('options') ? array_map(function ($text, $id) {
            return (object)['id' => $id, 'option_text' => $text];
        }, old('options'), old('options_id')) : ($pollOptions ?? []);

        if (empty($options)) {
            $options = [(object)['id' => '', 'option_text' => '']];
        }

        return view('admin/poll/form', [
            'title'          => trans("update_poll"),
            'action'         => $action,
            'poll'           => $poll,
            'options'        => $options,
            'breadcrumbLink' => ['label' => trans("polls"), 'url' => adminUrl('polls')]
        ]);
    }

    /**
     * AJAX Endpoint: Delete Poll
     *
     * @method POST
     */
    public function deletePoll()
    {
        checkPermission('polls');

        $pollModel = model('PollModel');

        $id = (int)inputPost('id');

        $poll = $pollModel->find($id);
        if (!$poll) {
            setErrorMessage('msg_error');
            return jsonResponse(false);
        }

        if ($pollModel->deletePoll($poll->id)) {
            setSuccessMessage("msg_deleted");
        } else {
            setErrorMessage("msg_error");
        }

        return jsonResponse(true);
    }

    /**
     * Contact Messages
     */
    public function contactMessages()
    {
        checkPermission('contact');

        $model = model('ContactModel');

        if (isPostMethod()) {
            $postData = $this->request->getPost();
            $submit = $postData['submit'] ?? '';

            if ($submit === 'send_email') {
                $emailService = new EmailService();

                $email = $postData['recipient'] ?? '';
                $subject = $postData['subject'] ?? 'Contact Form';
                $body = $postData['email_body'] ?? '';

                if ($emailService->sendContacEmail($email, $subject, $body)) {
                    setSuccessMessage('msg_email_sent');
                } else {
                    setErrorMessage('msg_error');
                }
            }

            return redirect()->to(getBackUrl());
        }

        return view('admin/contact_messages', [
            'title'    => trans("contact_messages"),
            'messages' => $model->findAllPaginated($this->perPage),
            'pager'    => $model->pager
        ]);
    }

    /**
     * AJAX Endpoint: Delete Contact Message
     *
     * @method POST
     */
    public function deleteContactMessage()
    {
        checkPermission('contact');

        $ids = inputPost('ids');

        $ids = is_array($ids) ? $ids : [$ids];
        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            return jsonResponse(false);
        }

        model("ContactModel")->delete($ids) ? setSuccessMessage("msg_deleted") : setErrorMessage("msg_error");

        return jsonResponse(true);
    }

    /**
     * Comments
     */
    public function comments()
    {
        $model = model('CommentModel');

        if (isPostMethod()) {

            if (!hasPermission('comments')) {
                return jsonResponse(false);
            }

            $action = inputPost('action');
            $ids = filterValidIds(inputPost('ids'));

            if (empty($action) || empty($ids)) {
                setErrorMessage('msg_error');
                return jsonResponse(false);
            }

            if ($action === 'delete') {
                $model->deleteCommentsAndReplies($ids) ? setSuccessMessage("msg_deleted") : setErrorMessage("msg_error");
            } elseif ($action === 'approve') {
                $model->approveComments($ids) ? setSuccessMessage("msg_updated") : setErrorMessage("msg_error");
            }

            return jsonResponse(false);
        }

        checkPermission('comments');

        $filters = [
            'status' => cleanStr(inputGet('status'))
        ];

        return view('admin/comments', [
            'title'         => trans("comments"),
            'comments'      => $model->findAllPaginated($filters, $this->perPage),
            'pager'         => $model->pager,
            'panelSettings' => panelSettings()
        ]);
    }

    /**
     * Newsletter
     */
    public function newsletter()
    {
        checkPermission('newsletter');

        $model = model('NewsletterModel');
        $userModel = model('UserModel');

        if (isPostMethod()) {

            $postData = $this->request->getPost();
            $submit = $postData['submit'] ?? '';

            // Import
            if ($submit === 'import') {
                try {
                    $service = new NewsletterService();
                    $result = $service->import($this->request->getFile('file'));

                    if ($result['success'] > 0) {
                        setSuccessMessage("operation_completed");
                    } else {
                        setErrorMessage("msg_error");
                    }
                } catch (\Exception $e) {
                    setErrorMessage("msg_error");
                }

                return redirect()->to(adminUrl('newsletter'));
            }

            // Export
            if ($submit === 'export') {
                (new NewsletterService())->export();
            }

            $config = $this->configModel->first();

            $oldImg = [
                'image'   => $config->newsletter_settings->image ?? '',
                'storage' => $config->newsletter_settings->storage ?? ''
            ];

            $config->fill($postData);

            $uploadService = new UploadService($this->config);
            $newImgData = $uploadService->uploadNewsletterImage();

            $config->handleNewsletterSettings($postData, $newImgData);

            if ($this->configModel->trySave($config)) {
                setSuccessMessage("msg_updated");

                if (!empty($oldImg['image']) && !empty($newImgData)) {
                    deleteStorageFile($oldImg['image'], $oldImg['storage']);
                }
            } else {
                setErrorMessage("msg_error");
            }

            return redirect()->to(adminUrl('newsletter'));
        }

        return view('admin/newsletter/newsletter', [
            'title'                => trans("newsletter"),
            'panelSettings'        => panelSettings(),
            'newsletterEmailCount' => $model->countAll(),
            'usersCount'           => $userModel->countAll()
        ]);
    }

    /**
     * AJAX Endpoint: Sends newsletter batch of emails
     *
     * @method POST
     */
    public function sendNewsletterBatch()
    {
        if (!$this->request->isAJAX()) {
            return jsonResponse(false);
        }

        checkPermission('newsletter');

        $subject = inputPost('subject');
        $body = inputPost('body');
        $emails = array_unique((array)inputPost('emails'));
        $type = inputPost('type');

        if (empty($subject) || empty($body) || empty($emails) || !in_array($type, ['users', 'newsletter'])) {
            return jsonResponse(['result' => 0, 'error' => 'Invalid parameters.']);
        }

        $recipientsToSend = [];

        if ($type === 'newsletter') {
            $model = model('NewsletterModel');
            $newsletterRows = $model->whereIn('email', $emails)->asArray()->findAll();

            $newsletterByEmail = [];
            foreach ($newsletterRows as $row) {
                $newsletterByEmail[$row['email']] = $row;
            }

            foreach ($emails as $email) {
                if (!isset($newsletterByEmail[$email])) continue;

                $entry = $newsletterByEmail[$email];
                $token = $entry['token'];

                if (empty($token)) {
                    $token = generateSecureToken();
                    $model->update($entry['id'], ['token' => $token]);
                }

                $recipientsToSend[] = (object)['email' => $email, 'token' => $token];
            }
        } else {
            $userModel = model('UserModel');
            $users = $userModel->whereIn('email', $emails)->select('email')->asArray()->findAll();

            foreach ($users as $user) {
                $recipientsToSend[] = (object)['email' => $user['email'], 'token' => null];
            }
        }

        if (empty($recipientsToSend)) {
            return jsonResponse(['result' => 1, 'message' => 'No valid recipients found to send.']);
        }

        $emailService = new EmailService();
        $successCount = 0;

        foreach ($recipientsToSend as $recipient) {
            if ($emailService->sendNewsletterEmail($recipient, $subject, $body)) {
                $successCount++;
            }
        }

        return jsonResponse([
            'result'  => ($successCount > 0) ? 1 : 0,
            'message' => "Successfully sent $successCount of " . count($recipientsToSend) . " emails."
        ]);
    }

    /**
     * AJAX Endpoint: Delete Newsletter Email
     *
     * @method POST
     */
    public function deleteNewsletterEmail()
    {
        if (!$this->request->isAJAX()) {
            return jsonResponse(false);
        }

        if (!hasPermission('newsletter')) {
            return jsonResponse(false);
        }

        $model = model('NewsletterModel');

        $id = (int)inputPost('id');
        $model->delete($id);

        return jsonResponse(true);
    }

    /**
     * Ad Spaces
     */
    public function adSpaces()
    {
        checkPermission('ad_spaces');

        $model = model('AdSpaceModel');

        $adSpaceKey = inputGet('ad_space', 'header');
        $langId = inputGet('lang') ?? $this->activeLang->id;

        $lang = model('LanguageModel')->find($langId);
        if (empty($lang)) {
            $langId = $this->activeLang->id;
        }

        $adSpace = $model->findByKey($langId, $adSpaceKey);
        if (empty($adSpace)) {
            return redirect()->to(adminUrl('ad-spaces'));
        }

        return view('admin/ad_spaces', [
            'title'         => trans('ad_spaces'),
            'adSpaceKey'    => $adSpaceKey,
            'langId'        => $langId,
            'panelSettings' => panelSettings(),
            'categories'    => model('CategoryModel')->findAllRoots($langId, true),
            'adSpace'       => $adSpace,
            'banners'       => model('AdBannerModel')->where('ad_space_id', $adSpace->id)->findAll(),
            'arrayAdSpaces' => [
                'header'       => trans('ad_space_header'),
                'index_top'    => trans('ad_space_index_top'),
                'index_bottom' => trans('ad_space_index_bottom'),
                'post_top'     => trans('ad_space_post_top'),
                'post_bottom'  => trans('ad_space_post_bottom'),
                'posts_top'    => trans('ad_space_posts_top'),
                'posts_bottom' => trans('ad_space_posts_bottom'),
                'sidebar_1'    => trans('sidebar') . '-1',
                'sidebar_2'    => trans('sidebar') . '-2',
                'in_article_1' => trans('ad_space_in_article') . '-1',
                'in_article_2' => trans('ad_space_in_article') . '-2',
            ],
        ]);
    }

    /**
     * Ad Spaces Post
     */
    public function adSpacesPost()
    {
        checkPermission('ad_spaces');

        $model = model('AdSpaceModel');
        $uploadService = new UploadService($this->config);

        $postData = $this->request->getPost();

        $file = $this->request->getFile('file_ad_code_desktop');
        if ($file && $file->isValid()) {
            $result = $uploadService->uploadRawFile($file, [
                'folderName'        => 'blocks',
                'prefix'            => 'block_',
                'allowedExtensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp']
            ]);

            if (!empty($result['path']) && !empty($result['storage'])) {
                $postData['ad_code_desktop'] = createAdCode($postData['url_ad_code_desktop'], $result['path'], $result['storage'], $postData['desktop_width'], $postData['desktop_height']);
            }
        }

        $file = $this->request->getFile('file_ad_code_mobile');
        if ($file && $file->isValid()) {
            $result = $uploadService->uploadRawFile($file, [
                'folderName'        => 'blocks',
                'prefix'            => 'block_',
                'allowedExtensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp']
            ]);

            if (!empty($result['path']) && !empty($result['storage'])) {
                $postData['ad_code_mobile'] = createAdCode($postData['url_ad_code_mobile'], $result['path'], $result['storage'], $postData['mobile_width'], $postData['mobile_height']);
            }
        }

        if ($model->updateAdSpace($postData)) {
            setSuccessMessage("msg_updated");
        } else {
            setErrorMessage("msg_error");
        }

        return redirect()->to(getBackUrl());
    }

    /**
     * Save/Update Ad Banner
     */
    public function saveAdBannerPost()
    {
        checkPermission('ad_spaces');

        $bannerModel = model('AdBannerModel');
        $uploadService = new UploadService($this->config);

        $bannerId = $this->request->getPost('id');
        $adSpaceId = $this->request->getPost('ad_space_id');

        if (empty($adSpaceId)) {
            setErrorMessage("msg_error");
            return redirect()->to(getBackUrl());
        }

        $expiryDate = $this->request->getPost('expiry_date');
        if (empty($expiryDate)) {
            $expiryDate = null;
        }

        $data = [
            'ad_space_id'         => (int)$adSpaceId,
            'banner_url_desktop'  => $this->request->getPost('banner_url_desktop'),
            'banner_url_mobile'   => $this->request->getPost('banner_url_mobile'),
            'ad_code_desktop'     => $this->request->getPost('ad_code_desktop'),
            'ad_code_mobile'      => $this->request->getPost('ad_code_mobile'),
            'expiry_date'         => $expiryDate
        ];

        // Find existing record if updating
        $existing = null;
        if (!empty($bannerId)) {
            $existing = $bannerModel->find($bannerId);
        }

        // Upload desktop banner file
        $fileDesktop = $this->request->getFile('file_ad_code_desktop');
        if ($fileDesktop && $fileDesktop->isValid()) {
            // Delete old file from storage
            if (!empty($existing) && !empty($existing->banner_path_desktop)) {
                deleteStorageFile($existing->banner_path_desktop, $existing->banner_storage_desktop);
            }

            $result = $uploadService->uploadRawFile($fileDesktop, [
                'folderName'        => 'blocks',
                'prefix'            => 'block_',
                'allowedExtensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp']
            ]);

            if (!empty($result['path']) && !empty($result['storage'])) {
                $data['banner_path_desktop'] = $result['path'];
                $data['banner_storage_desktop'] = $result['storage'];
            }
        }

        // Upload mobile banner file
        $fileMobile = $this->request->getFile('file_ad_code_mobile');
        if ($fileMobile && $fileMobile->isValid()) {
            // Delete old file from storage
            if (!empty($existing) && !empty($existing->banner_path_mobile)) {
                deleteStorageFile($existing->banner_path_mobile, $existing->banner_storage_mobile);
            }

            $result = $uploadService->uploadRawFile($fileMobile, [
                'folderName'        => 'blocks',
                'prefix'            => 'block_',
                'allowedExtensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp']
            ]);

            if (!empty($result['path']) && !empty($result['storage'])) {
                $data['banner_path_mobile'] = $result['path'];
                $data['banner_storage_mobile'] = $result['storage'];
            }
        }

        if (!empty($existing)) {
            if ($bannerModel->update($bannerId, $data)) {
                setSuccessMessage("msg_updated");
            } else {
                setErrorMessage("msg_error");
            }
        } else {
            $data['status'] = 1;
            if ($bannerModel->insert($data)) {
                setSuccessMessage("msg_updated");
            } else {
                setErrorMessage("msg_error");
            }
        }

        return redirect()->to(getBackUrl());
    }

    /**
     * Delete Ad Banner
     */
    public function deleteAdBannerPost()
    {
        checkPermission('ad_spaces');

        $bannerModel = model('AdBannerModel');
        $id = $this->request->getPost('id');

        if (!empty($id)) {
            $banner = $bannerModel->find($id);
            if (!empty($banner)) {
                // Delete files from storage
                if (!empty($banner->banner_path_desktop)) {
                    deleteStorageFile($banner->banner_path_desktop, $banner->banner_storage_desktop);
                }
                if (!empty($banner->banner_path_mobile)) {
                    deleteStorageFile($banner->banner_path_mobile, $banner->banner_storage_mobile);
                }

                if ($bannerModel->delete($id)) {
                    setSuccessMessage("msg_deleted");
                } else {
                    setErrorMessage("msg_error");
                }
            } else {
                setErrorMessage("msg_error");
            }
        } else {
            setErrorMessage("msg_error");
        }

        return redirect()->to(getBackUrl());
    }

    /**
     * AJAX Endpoint: Toggle Ad Banner Status
     */
    public function toggleAdBannerStatusPost()
    {
        checkPermission('ad_spaces');

        $id = (int)inputPost('id');
        $status = (int)inputPost('status');

        $bannerModel = model('AdBannerModel');
        $banner = $bannerModel->find($id);
        if (empty($banner)) {
            return jsonResponse(false, 404);
        }

        if ($bannerModel->update($id, ['status' => $status])) {
            return jsonResponse(true);
        }

        return jsonResponse(false, 500);
    }

    /**
     * Adsense Code Post
     */
    public function adsenseCodePost()
    {
        checkPermission('ad_spaces');

        $postData = $this->request->getPost();

        $config = $this->configModel->first();

        $config->fill($postData);

        $this->configModel->trySave($config) ? setSuccessMessage("msg_updated") : setErrorMessage("msg_error");

        return redirect()->to(adminUrl('ad-spaces'));
    }

    /**
     * AJAX Endpoint: Populate users dropdown
     *
     * @method POST
     */
    public function populateUsersDropdown()
    {
        $q = cleanStr(inputPost('q'));

        $users = model('UserModel')->findAllByUsername($q, 100);

        return jsonResponse(['items' => $users]);
    }

    /**
     * Seo Tools
     */
    public function seoTools()
    {
        checkPermission('seo_tools');

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            $config = $this->configModel->first();

            $config->fill($postData);

            $this->configModel->trySave($config) ? setSuccessMessage("msg_updated") : setErrorMessage("msg_error");

            return redirect()->to(getBackUrl());
        }

        $langId = inputGet('lang');
        if (empty($langId)) {
            $langId = $this->activeLang->id;
        }

        return view('admin/seo_tools', [
            'title'         => trans("seo_tools"),
            'langId'        => $langId,
            'seoSettings'   => $this->settingsModel->findByLangId($langId),
            'panelSettings' => panelSettings()
        ]);
    }

    /**
     * Sitemap Post
     */
    public function sitemapPost()
    {
        checkPermission('seo_tools');

        $submit = inputPost('submit');

        $configEntity = $this->configModel->first();

        $postData = $this->request->getPost();

        $service = new SitemapService($this->config);

        if ($submit === 'settings') {

            $configEntity->handleSitemapSettings($postData);

            if (model('ConfigModel')->trySave($configEntity)) {
                try {
                    $service->generate();
                    setSuccessMessage('msg_updated');
                } catch (\Throwable $e) {
                    log_message('error', '[Sitemap Generate Error] ' . $e->getMessage());
                    setErrorMessage('msg_error');
                }
            } else {
                setErrorMessage('msg_error');
            }

            return redirect()->to(adminUrl('seo-tools'));

        }

        if ($submit === 'download') {
            $fullPath = FCPATH . 'sitemap.xml';

            if (!file_exists($fullPath) || strpos(realpath($fullPath), FCPATH) !== 0) {
                setErrorMessage('msg_error');
                return redirect()->to(adminUrl('seo-tools'));
            }

            return $this->response->download($fullPath, null);

        } elseif ($submit === 'delete') {
            $service->deleteOldSitemaps();
            setSuccessMessage('msg_deleted');
        }

        return redirect()->to(adminUrl('seo-tools'));
    }

    /**
     * Storage
     */
    public function storage()
    {
        checkPermission('storage');

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            $config = $this->configModel->first();

            $config->fill($postData);

            $config->handleStorageSettings($postData);

            $this->configModel->trySave($config) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

            return redirect()->to(adminUrl('storage'));
        }

        return view('admin/storage', [
            'title'           => trans("storage"),
            'panelSettings'   => panelSettings(),
            'storageOptions'  => getAppDefault('cloudStorages'),
            'storageSettings' => $this->config->storage_settings
        ]);
    }

    /**
     * Cache System
     */
    public function cacheSystem()
    {
        checkPermission('cache_system');

        if (isPostMethod()) {
            $submit = inputPost('submit');

            if (!empty($submit)) {
                $cacheGroup = match ($submit) {
                    'reset_content_cache' => 'content',
                    default => 'all'
                };

                service('cacheService')->reset($cacheGroup) ? setSuccessMessage('msg_reset_cache') : setErrorMessage('msg_error');

            } else {
                $postData = $this->request->getPost();

                if (!empty($postData['content_cache_ttl'])) {
                    $postData['content_cache_ttl'] = $postData['content_cache_ttl'] * 60;
                }

                $config = $this->configModel->first();

                $config->fill($postData);

                $this->configModel->trySave($config) ? setSuccessMessage("msg_updated") : setErrorMessage("msg_error");

                return redirect()->to(getBackUrl());
            }

            return redirect()->to(getBackUrl());
        }

        return view('admin/cache_system', [
            'title'         => trans("cache_system"),
            'panelSettings' => panelSettings()
        ]);
    }

    /**
     * Google News
     */
    public function googleNews()
    {
        checkPermission('google_news');

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            $config = $this->configModel->first();

            $config->fill($postData);

            $config->handleGoogleNewsSettings($postData);

            $this->configModel->trySave($config) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

            return redirect()->to(getBackUrl());
        }

        return view('admin/google_news', [
            'title'         => trans("google_news"),
            'panelSettings' => panelSettings()
        ]);
    }

    /**
     * Security
     */
    public function security()
    {
        checkPermission('security');

        if (isPostMethod()) {

            $postData = $this->request->getPost();
            $submit = $postData['submit'] ?? '';

            $config = $this->configModel->first();
            $config->fill($postData);

            if ($submit === 'general') {

                $config->handleSecuritySettings($postData);

                $this->configModel->trySave($config) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

            } elseif ($submit === 'captcha') {

                $config->handleCaptchaSettings($postData);

                $this->configModel->trySave($config) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

            } elseif ($submit === 'cron') {

                $config->handleCronSecretKey($postData);

                if ($this->configModel->trySave($config)) {
                    return jsonResponse([
                        'status' => 1,
                        'token'  => $config->cron_secret_key
                    ]);
                } else {
                    return jsonResponse(false);
                }
            }

            return redirect()->to(getBackUrl());
        }

        return view('admin/security/settings', [
            'title'            => trans("security"),
            'action'           => adminUrl('security/settings'),
            'panelSettings'    => panelSettings(),
            'securitySettings' => getSecuritySettings()
        ]);
    }

    /**
     * Email Blacklist
     */
    public function emailBlacklist()
    {
        checkPermission('security');

        $model = model('EmailBlacklistModel');

        if (isPostMethod()) {

            $postData = $this->request->getPost();

            $email = cleanStr($postData['email'] ?? '');
            if (!empty($email)) {
                $model->addEmail($email);
                setSuccessMessage('msg_added');
            }

            return redirect()->to(getBackUrl());
        }

        $filters = [
            'email' => cleanStr(inputGet('q'))
        ];

        return view('admin/security/email_blacklist', [
            'title'                => trans("email_blacklist"),
            'action'               => adminUrl('security/email-blacklist'),
            'panelSettings'        => panelSettings(),
            'blacklistEmails'      => $model->findAllPaginated($filters, $this->perPage),
            'totalBlacklistEmails' => $model->pager->getTotal(),
            'pager'                => $model->pager,
        ]);
    }

    /**
     * AJAX Endpoint: Delete blacklist email
     *
     * @method POST
     */
    public function deleteBlacklistEmail()
    {
        checkPermission('security');

        $id = (int)inputPost('id');

        if (model('EmailBlacklistModel')->delete($id)) {
            setSuccessMessage('msg_updated');
        } else {
            setErrorMessage('msg_error');
        }

        return jsonResponse(true);
    }

    /**
     * Email Settings
     */
    public function emailSettings()
    {
        checkPermission('settings');

        if (isPostMethod()) {
            $submit = inputPost('submit');
            $postData = $this->request->getPost();

            if ($submit === 'test_email') {

                $isSent = false;

                if (!empty($postData['email'])) {
                    $subject = "{$this->settings->application_name} Test Email";

                    $emailService = new EmailService();
                    if ($emailService->sendTestEmail($postData['email'], $subject)) {
                        $isSent = true;
                    }
                }

                $isSent ? setSuccessMessage('msg_email_sent') : setErrorMessage('msg_error');

            } else {
                $config = $this->configModel->first();

                $config->fill($postData);

                $config->handleEmailSettings($postData);

                $this->configModel->trySave($config) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');
            }

            return redirect()->to(adminUrl('email-settings'));
        }

        // Prepare view data
        $emailSettings = $this->config->email_settings;
        $emailServices = getAppDefault('emailServices');
        $validServices = array_keys($emailServices);
        $allowedProtocols = ['smtp', 'mail'];

        $service = inputGet('service') ?: ($emailSettings->mail_service ?: 'php-mailer');
        $protocol = inputGet('protocol') ?: ($emailSettings->mail_protocol ?: 'smtp');

        $template = $this->config->email_settings->email_template ?? 'pure_minimalist';

        $data = [
            'title'         => trans("email_settings"),
            'service'       => in_array($service, $validServices) ? $service : 'php-mailer',
            'protocol'      => in_array($protocol, $allowedProtocols) ? $protocol : 'smtp',
            'panelSettings' => panelSettings(),
            'emailServices' => $emailServices,
            'emailSettings' => $emailSettings,
            'template'      => $template
        ];

        return view('admin/settings/email_settings', $data);
    }

    /**
     * Font Settings
     */
    public function fonts()
    {
        checkPermission('settings');

        if (isPostMethod()) {
            $postData = $this->request->getPost();
            $submit = inputPost('submit');
            $langId = (int)inputPost('lang_id');

            if (empty($langId)) {
                return redirect()->to(getBackUrl());
            }

            $settings = $this->settingsModel->findByLangId($langId);

            if ($submit == 'settings') {

                $settings->fill($postData);
                $settings->handleFontSettings($postData);

                $this->settingsModel->trySave($settings) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

                return redirect()->to(adminUrl('fonts'));

            } elseif ($submit == 'font_size') {

                $settings->fill($postData);
                $settings->handleFontSizeSettings($postData);

                $this->settingsModel->trySave($settings) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

                return redirect()->to(adminUrl('fonts'));
            }

            return redirect()->to(getBackUrl());
        }

        $langId = (int)inputGet('lang');
        if (empty($langId)) {
            $langId = $this->activeLang->id;
        }

        $model = model('FontModel');

        return view('admin/font/fonts', [
            'title'         => trans("font_settings"),
            'panelSettings' => panelSettings(),
            'fonts'         => $model->findAllPaginated($this->perPage),
            'fontsAll'      => $model->findAll(),
            'pager'         => $model->pager,
            'langId'        => $langId
        ]);
    }

    /**
     * Add/Edit Font
     */
    public function addFont()
    {
        checkPermission('settings');

        $fontId = (int)inputPost('id');
        $postData = $this->request->getPost();

        $model = model('FontModel');
        $uploadService = new UploadService($this->config);

        foreach ([400, 600, 700] as $weight) {
            $fileKey = "font_file_{$weight}";
            $pathKey = "path_{$weight}";

            $uploadedPath = $uploadService->uploadFontFile($fileKey);

            if (!empty($uploadedPath)) {
                $postData[$pathKey] = $uploadedPath;
            }
        }

        if (!empty($fontId)) {
            $oldFont = $model->find($fontId);

            if (empty($oldFont)) {
                return redirect()->to(getBackUrl());
            }

            $postData['id'] = $fontId;

            if ($model->updateFont($postData)) {
                foreach ([400, 600, 700] as $weight) {
                    $pathKey = "path_{$weight}";
                    if (isset($postData[$pathKey]) && !empty($oldFont->$pathKey)) {
                        deleteStorageFile($oldFont->$pathKey, 'local');
                    }
                }

                setSuccessMessage("msg_updated");
            } else {
                setErrorMessage("msg_error");
            }

            return redirect()->to(getBackUrl());
        }

        if ($model->create($postData)) {
            setSuccessMessage("msg_added");
        } else {
            setErrorMessage("msg_error");
        }

        return redirect()->to(getBackUrl());
    }

    /**
     * AJAX Endpoint: Delete Font
     *
     * @method POST
     */
    public function deleteFont()
    {
        checkPermission('settings');

        $id = (int)inputPost('id');

        $model = model('FontModel');

        $font = $model->find($id);
        if (empty($font) || $font->is_default) {
            return jsonResponse(false);
        }

        if ($model->delete($id)) {
            foreach ([400, 600, 700] as $weight) {
                $pathKey = "path_{$weight}";
                deleteStorageFile($font->$pathKey, 'local');
            }

            setSuccessMessage('msg_deleted');
        } else {
            setErrorMessage('msg_error');
        }

        return jsonResponse(true);
    }

    /**
     * Global Settings
     */
    public function globalSettings()
    {
        checkPermission('settings');

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            $submit = esc(inputPost('submit')) ?: 'general';
            $redirectUrl = adminUrl('global-settings');

            if ($submit === 'routes') {
                $adminRoute = esc($postData['admin']) ?? 'admin';
                $redirectUrl = base_url($adminRoute . '/global-settings');
            }

            $config = $this->configModel->first();
            $config->fill($postData);

            $uploadService = new UploadService($this->config);

            $oldIconsToDelete = null;
            $oldLogosToDelete = [];
            $oldMtcMode = [];

            switch ($submit) {
                case 'general':
                    $oldIcons = $config->app_icon ?? [];

                    $newIconData = $uploadService->uploadAppIcon('app_icon');

                    $config->handleGlobalSettings($postData, $newIconData);

                    if (!empty($newIconData)) {
                        $oldIconsToDelete = $oldIcons;
                    }
                    break;

                case 'logo':
                    $logoPaths = [];
                    $currentLogos = $config->logo ?? null;
                    $keys = ['logo', 'logo_png', 'logo_dark', 'logo_dark_png'];

                    foreach ($keys as $key) {
                        $path = $uploadService->uploadLogo($key);
                        if (!empty($path)) {
                            $logoPaths[$key] = $path;

                            if (is_object($currentLogos) && !empty($currentLogos->{$key})) {
                                $oldLogosToDelete[] = $currentLogos->{$key};
                            } elseif (is_array($currentLogos) && !empty($currentLogos[$key])) {
                                $oldLogosToDelete[] = $currentLogos[$key];
                            }
                        }
                    }

                    $config->handleLogoSettings($postData, $logoPaths);
                    break;

                case 'social_login':
                    $config->handleSocialLoginSettings($postData);
                    break;

                case 'routes':
                    $config->handleRouteSettings($postData);
                    break;

                case 'maintenance_mode':

                    $oldMtcMode = $config->maintenance_mode_settings;

                    $uploadedImage = $uploadService->uploadMaintenanceModeImage('image');

                    $config->handleMaintenanceModeSettings($postData, $uploadedImage);

                    if (empty($uploadedImage)) {
                        $oldMtcMode = null;
                    }

                    break;
            }

            if ($this->configModel->trySave($config)) {

                // Physically delete old app icon from server
                if (!empty($oldIconsToDelete)) {
                    $uploadService->deleteOldAppIcon((array)$oldIconsToDelete);
                }

                // Physically delete old files from server
                if (!empty($oldLogosToDelete)) {
                    foreach ($oldLogosToDelete as $oldPath) {
                        $fullPath = FCPATH . ltrim($oldPath, '/');
                        if (is_file($fullPath)) {
                            @unlink($fullPath);
                        }
                    }
                }

                if (!empty($oldMtcMode) && !empty($oldMtcMode->image) && !empty($oldMtcMode->storage)) {
                    deleteStorageFile($oldMtcMode->image, $oldMtcMode->storage);
                }
                setSuccessMessage('msg_updated');
            } else {
                setErrorMessage('msg_error');
            }

            if ($submit == 'maintenance_mode') {
                $submit = 'general';
            }

            return redirect()->to($redirectUrl . "?active_tab={$submit}");
        }

        $langId = (int)inputGet('lang', $this->activeLang->id);
        $activeTab = inputGet('active_tab', 'general');

        $maintenanceMode = $this->config->maintenance_mode_settings ?? null;
        $maintenanceModeImage = base_url('assets/media/maintenance_bg.jpg');
        if (is_object($maintenanceMode) && !empty($maintenanceMode->image)) {
            $maintenanceModeImage = getStorageFileUrl($maintenanceMode->image, $maintenanceMode->storage ?? null);
        }

        return view('admin/settings/global_settings', [
            'title'                => trans('global_settings'),
            'langId'               => $langId,
            'settings'             => $this->settingsModel->findByLangId((int)$langId),
            'panelSettings'        => panelSettings(),
            'routes'               => $this->config->routes,
            'maintenanceMode'      => $maintenanceMode,
            'maintenanceModeImage' => $maintenanceModeImage,
            'activeTab'            => $activeTab,
        ]);
    }

    /**
     * Localized Settings
     */
    public function localizedSettings()
    {
        checkPermission('settings');

        if (isPostMethod()) {

            $postData = $this->request->getPost();
            $langId = (int)($postData['lang_id'] ?? 0);
            $submit = $postData['submit'] ?? '';

            $redirectUrl = adminUrl('localized-settings') . '?lang=' . $langId . '&active_tab=' . $submit;

            $settings = $this->settingsModel->findByLangId((int)$langId);

            $settings->fill($postData);

            if ($submit === 'general') {

                if (empty($postData['application_name'])) {
                    setErrorMessage('msg_error');
                    return redirect()->to(getBackUrl());
                }

                $settings->handleLocalizedSettings($postData);
            } elseif ($submit === 'social_media') {
                $settings->handleSocialMedia($postData);
            } elseif ($submit === 'cookies_warning') {
                $settings->handleCookiesWarningSettings($postData);
            }

            if ($this->settingsModel->trySave($settings)) {
                setSuccessMessage('msg_updated');
            } else {
                setErrorMessage('msg_error');
            }

            return redirect()->to($redirectUrl);
        }

        $langId = (int)inputGet('lang', $this->activeLang->id);
        $activeTab = inputGet('active_tab', 'general');

        return view('admin/settings/localized_settings', [
            'title'         => trans('localized_settings'),
            'langId'        => $langId,
            'settings'      => $this->settingsModel->findByLangId((int)$langId),
            'panelSettings' => panelSettings(),
            'activeTab'     => $activeTab,
        ]);
    }

    /**
     * Content settings
     */
    public function contentSettings()
    {
        checkPermission('settings');

        $postFormats = array_keys(getAppDefault('postFormats'));

        if (isPostMethod()) {

            $submit = inputPost('submit');
            $postData = $this->request->getPost();

            $config = $this->configModel->first();

            // Define default redirect URL
            $redirectUrl = adminUrl("content-settings");

            switch ($submit) {
                // Save featured content settings
                case 'featured_content':

                    $config->fill($postData);

                    $config->handleFeaturedContentSettings($postData);

                    $this->configModel->trySave($config) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

                    $contentTab = $postData['content_tab'] ?? 'main_slider';

                    $redirectUrl = adminUrl("content-settings?content_tab={$contentTab}");

                    return redirect()->to($redirectUrl);

                // Save AI writer settings
                case 'ai_writer':

                    $config->fill($postData);

                    $config->handleAiWriterSettings($postData);

                    $this->configModel->trySave($config) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

                    return redirect()->to($redirectUrl);

                // Save auto post deletion settings
                case 'auto_post_deletion':

                    $config->fill($postData);

                    $config->handleAutoPostDeletionSettings($postData);

                    $this->configModel->trySave($config) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

                    return redirect()->to($redirectUrl);

                // Save general content settings
                default:
                    $activeTab = inputPost('submit') ?? 'general';

                    $config->fill($postData);

                    // Set post formats
                    if ($activeTab == 'post_formats') {
                        $config->handlePostFormatsPreferences($postData, $postFormats);
                    } elseif ($activeTab == 'file_upload') {
                        $config->handleFileUploadSettings($postData);
                    }

                    $this->configModel->trySave($config) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

                    return redirect()->to($redirectUrl . '?active_tab=' . $activeTab);
            }
        }

        $activeTab = inputGet('active_tab', 'general');

        $allowedFileExtensions = implode(',', $this->config->allowed_file_extensions->toArray() ?? []);

        return view('admin/settings/content_settings', [
            'title'                 => trans("content_settings"),
            'action'                => adminUrl("content-settings"),
            'panelSettings'         => panelSettings(),
            'postFormats'           => $postFormats,
            'maxShowcasePostsLimit' => defined('LIMIT_SHOWCASE_POSTS') ? (int)LIMIT_SHOWCASE_POSTS : 50,
            'activeTab'             => $activeTab,
            'allowedFileExtensions' => $allowedFileExtensions
        ]);
    }

    /**
     * Payment settings
     */
    public function paymentSettings()
    {
        checkPermission('settings');

        $gatewayModel = model('PaymentGatewayModel');
        $countryModel = model('CountryModel');
        $settingsModel = model('SettingsModel');

        $currencies = getCurrencies();
        $gateways = $gatewayModel->findAll();

        $activeTab = inputGet('active_tab', 'paypal');

        if (isPostMethod()) {

            $submit = inputPost('submit');
            $postData = $this->request->getPost();

            $config = $this->configModel->first();

            $redirectUrl = adminUrl("payment-settings");

            if ($submit === 'currency') {

                $selectedCode = $postData['currency_code'] ?? 'USD';
                $default = $currencies[$selectedCode] ?? ($currencies['USD'] ?? null);

                $config->handleCurrencySettings($postData, $default);

                $this->configModel->trySave($config) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

            } elseif ($submit === 'gateway') {

                $activeTab = inputPost('active_tab', 'paypal');

                $id = $postData['gateway_id'] ?? 0;

                $gatewayModel->updateGateway($id, $postData) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

                return redirect()->to($redirectUrl . '?active_tab=' . esc($activeTab));

            } elseif ($submit === 'tax') {

                $vatRates = $postData['vat_rates'] ?? [];

                $countryModel->updateVatRates($vatRates) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

            } elseif ($submit === 'invoice_details') {
                foreach ($this->activeLanguages as $lang) {

                    $settings = $settingsModel->findByLangId((int)$lang->id);
                    if ($settings) {
                        $settings->handleInvoiceDetails($postData, (int)$lang->id);
                        $this->settingsModel->trySave($settings);
                    }
                }

                $this->configModel->updateConfig(['invoice_prefix' => $postData['invoice_prefix'] ?? 'INV']);

                setSuccessMessage('msg_updated');
            }

            return redirect()->to($redirectUrl);
        }

        $settingsData = $settingsModel->findAll();
        $settingsMap = [];
        foreach ($settingsData as $row) {
            $settingsMap[$row->lang_id] = $row;
        }

        return view('admin/settings/payment_settings', [
            'title'       => trans("payment_settings"),
            'action'      => adminUrl("payment-settings"),
            'currencies'  => $currencies,
            'gateways'    => $gateways,
            'countries'   => $countryModel->getCountries(true),
            'activeTab'   => $activeTab,
            'settingsMap' => $settingsMap,
        ]);
    }

    /**
     * Control Panel Language Post
     */
    public function setActiveLanguagePost()
    {
        $langId = (int)inputPost('lang_id');

        $language = model('LanguageModel')->find($langId);
        if (!empty($language)) {
            $this->session->set('vr_control_panel_lang', $language->id);
        }

        return redirect()->to(getBackUrl());
    }

    /**
     * Download Database Backup
     */
    public function downloadDatabaseBackup()
    {
        if (!isSuperAdmin()) {
            return redirect()->to(adminUrl());
        }

        $db = \Config\Database::connect();
        $backupService = new DatabaseBackupService($db);

        // Execute the backup with your preferences
        $sql = $backupService->downloadBackup([
            'foreign_key_checks' => false,
            'add_drop'           => true
        ]);

        $filename = 'db_backup_' . date('Ymd_His') . '.sql';

        return $this->response->download($filename, $sql);
    }
}
