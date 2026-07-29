<?php

namespace App\Controllers;

use App\Libraries\AIWriter;
use App\Services\QuizService;

class PostController extends BaseAdminController
{
    protected $postModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->postModel = model('PostModel');

        // If email verification is enabled, only users who have verified their email address will be able to access
        if ($this->config->email_verification == 1 && user()->email_status == 0) {
            setErrorMessage("email_verification_required");
            redirect()->to(adminUrl())->send();
            exit;
        }
    }

    /**
     * Post Format
     */
    public function postFormat()
    {
        checkPermission('add_post');

        $postFormats = $this->config->post_formats ?? null;

        return view('admin/post/post_format', [
            'title'          => trans("post_format"),
            'postFormats'    => $postFormats,
            'breadcrumbLink' => ['label' => trans("posts"), 'url' => adminUrl('posts')]
        ]);
    }

    /**
     * Posts
     */
    public function posts($status = null)
    {
        checkPermission('add_post');

        if (empty($status) || !in_array($status, ['pending', 'scheduled', 'draft'])) {
            $status = 'published';
        }

        $statusMap = [
            'pending'   => ['pending_posts', 'pending-posts'],
            'scheduled' => ['scheduled_posts', 'scheduled-posts'],
            'draft'     => ['drafts', 'drafts'],
        ];

        $title = trans('posts');
        $action = adminUrl('posts');
        $listType = 'posts';
        if (isset($statusMap[$status])) {
            $title = trans($statusMap[$status][0]);
            $action = adminUrl($statusMap[$status][1]);
            $listType = $statusMap[$status][0];
        }

        $postFormat = inputGet('post_format');
        $postFormats = array_keys(getAppDefault('postFormats'));
        if (!in_array($postFormat, $postFormats, true)) {
            $postFormat = '';
        }

        $filters = [
            'status'          => $status,
            'id'              => (int)inputGet('id'),
            'lang_id'         => !empty(inputGet('lang_id')) ? (int)inputGet('lang_id') : null,
            'premium_content' => inputGet('premium_content'),
            'display_type'    => inputGet('display_type'),
            'post_format'     => $postFormat,
            'category_id'     => (int)inputGet('category_id'),
            'user_id'         => (int)inputGet('user_id'),
            'q'               => cleanStr(inputGet('q')),
        ];

        if (!hasPermission('manage_all_posts')) {
            $filters['user_id'] = user()->id;
        }

        return view('admin/post/posts', [
            'title'                => $title,
            'action'               => $action,
            'status'               => $status,
            'listType'             => $listType,
            'panelSettings'        => panelSettings(),
            'posts'                => $this->postModel->findAllPaginatedAdmin($filters, $this->perPage),
            'pager'                => $this->postModel->pager,
            'postSelectionOptions' => getAppDefault('postSelectionOptions'),
        ]);
    }

    /**
     * Add Post
     */
    public function addPost()
    {
        checkPermission('add_post');

        $defaultPostFormats = array_keys(getAppDefault('postFormats'));

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            // Clean external links
            if (!empty($postData['content'])) {
                $postData['content'] = cleanExternalLinks($postData['content'], 'post', user()->id);
            }

            $post = new \App\Entities\Post();
            $post->fill($postData);

            $defaultLangId = $this->config->site_lang ?? 1;

            $post->handlePostForm($postData, $defaultPostFormats, (int)$defaultLangId, (int)user()->id);

            // Set event data
            if (!empty($postData['post_format']) && $postData['post_format'] === 'event') {
                $speakers = $postData['speakers'] ?? [];
                $avatarIds = [];
                if (is_array($speakers)) {
                    $avatarIds = array_column($speakers, 'avatar');
                    $avatarIds = array_filter($avatarIds);
                }

                $avatars = [];
                if (!empty($avatarIds)) {
                    $avatars = model('PostItemImageModel')->findByIds('event_avatar_image', $avatarIds);
                }

                $post->handleEventData($postData, $avatars);
            }

            // Manage post visibility
            $postVisibility = hasPermission('manage_all_posts')
                ? (int)($postData['visibility'] ?? 0)
                : ((int)($this->config?->require_approval_new_posts ?? 0) === 1 ? 0 : 1);

            $post->visibility = $postVisibility;

            if ($this->postModel->trySave($post)) {

                $newPostId = $this->postModel->getInsertID();
                $post = $this->postModel->find($newPostId);

                if (empty($post)) {
                    setErrorMessage("msg_error");
                    return redirect()->back()->withInput();
                }

                // Handle post relations
                $this->handlePostRelations($post, $postData);

                // Clear content cache
                if ($postVisibility === 1) {
                    clearContentCache();
                }

                $message = $this->getMessageWithPreviewUrl($post, 'add');

                setSuccessMessage($message, false);

                return redirect()->to(getBackUrl());
            } else {
                return redirect()->back()->withInput()->with('errors', $this->postModel->errors());
            }
        }

        $format = inputGet('format');
        if (!in_array($format, $defaultPostFormats)) {
            $format = 'article';
        }

        if (!isPostFormatActive((string)$format, $this->config)) {
            return redirect()->to(adminUrl('add-post/format'));
        }

        $showGenerateWithAi = false;
        if (in_array($format, ['article', 'video', 'audio', 'recipe', 'event'])) {
            $showGenerateWithAi = true;
        }

        return view('admin/post/form', [
            'title'                    => trans('add_' . $format),
            'action'                   => adminUrl('add-post'),
            'post'                     => null,
            'postFormat'               => $format,
            'postSelections'           => [],
            'postTags'                 => [],
            'panelSettings'            => panelSettings(),
            'showGenerateWithAi'       => $showGenerateWithAi,
            'breadcrumbLink'           => ['label' => trans("posts"), 'url' => adminUrl('posts')],
            'breadcrumbGenerateAiLink' => ($this->aiWriter->status && !empty($showGenerateWithAi)) ? true : false
        ]);
    }

    /**
     * Helper: Generates post return message with preview post URL
     */
    private function getMessageWithPreviewUrl($post, $action = 'add')
    {
        if (empty($post)) return;

        $message = $action === 'add' ? trans("msg_added") : trans("msg_updated");

        $baseUrl = generateBaseURLByLangId($post->lang_id);

        $postUrl = !isPostPublished($post) ? $baseUrl . 'post/preview/' . $post->slug : generatePostUrl($post, $baseUrl);

        $message .= '&nbsp;&nbsp;<a href="' . esc($postUrl) . '" target="_blank" class="alert-post-link font-w-700">' . trans("view_post") . '&nbsp;&nbsp;<i class="bi bi-box-arrow-up-right"></i></a>';

        return $message;
    }

    /**
     * Edit Post
     */
    public function editPost($id)
    {
        checkPermission('add_post');

        $post = $this->postModel->findById((int)$id);
        if (empty($post)) {
            return redirect()->to(adminUrl('posts'));
        }

        if (!checkPostOwnership($post->user_id)) {
            return redirect()->to(adminUrl('posts'));
        }

        $defaultPostFormats = array_keys(getAppDefault('postFormats'));

        if (isPostMethod()) {
            $postData = $this->request->getPost();

            // Clean external links
            if (!empty($postData['content'])) {
                $postData['content'] = cleanExternalLinks($postData['content'], 'post', user()->id);
            }

            $post->fill($postData);

            $defaultLangId = $this->config->site_lang ?? 1;

            $post->handlePostForm($postData, $defaultPostFormats, (int)$defaultLangId, (int)user()->id);

            // Set event data
            if (!empty($postData['post_format']) && $postData['post_format'] === 'event') {
                $speakers = $postData['speakers'] ?? [];
                $avatarIds = [];
                if (is_array($speakers)) {
                    $avatarIds = array_column($speakers, 'avatar');
                    $avatarIds = array_filter($avatarIds);
                }

                $avatars = [];
                if (!empty($avatarIds)) {
                    $avatars = model('PostItemImageModel')->findByIds('event_avatar_image', $avatarIds);
                }

                $post->handleEventData($postData, $avatars);
            }

            // Manage post visibility
            $post->visibility = hasPermission('manage_all_posts')
                ? (int)($postData['visibility'] ?? 0)
                : ((int)($this->config?->require_approval_edited_posts ?? 0) === 1 ? 0 : 1);

            if ($this->postModel->trySave($post)) {

                // Handle post relations
                $this->handlePostRelations($post, $postData);

                // Clear content cache
                clearContentCache();

                $message = $this->getMessageWithPreviewUrl($post, 'edit');

                setSuccessMessage($message, false);

                return redirect()->to(getBackUrl());
            } else {
                return redirect()->back()->withInput()->with('errors', $this->postModel->errors());
            }
        }

        // post tags
        $tagsStr = implode(',', array_column(model('TagModel')->findAllByPostId($post->id) ?: [], 'tag'));

        //post audios
        $postAudios = [];
        if ($post->post_format == 'audio') {
            $postAudios = model('MediaModel')->findAllMediaByPostId($post->id, 'audio');
        }

        // attachments
        $attachments = model('MediaModel')->findAllMediaByPostId($post->id, 'attachment');
        $attachmentIdsString = !empty($attachments) ? implode(',', array_column($attachments, 'id')) : '';

        // category data
        $categorySelectorData = model('CategoryModel')->selectorGetTree($post->category_id, $post->lang_id);

        // quiz data
        $quizQuestions = [];
        $quizAnswers = [];
        $quizResults = [];
        if (in_array($post->post_format, ['trivia_quiz', 'personality_quiz', 'poll'])) {
            $quizQuestions = model('QuizQuestionModel')->findAllByPostId($post->id);
            $quizAnswers = model('QuizAnswerModel')->findAllByPostId($post->id, true);
            $quizResults = model('QuizResultModel')->findAllByPostId($post->id);
        }

        // recipe data
        $dataRecipe = null;
        if ($post->post_format === 'recipe') {
            $dataRecipe = $post->post_data;
        }

        $showGenerateWithAi = false;
        if (in_array($post->post_format, ['article', 'video', 'audio', 'recipe', 'event'])) {
            $showGenerateWithAi = true;
        }

        return view('admin/post/form', [
            'title'                    => trans('update_' . $post->post_format),
            'action'                   => adminUrl('posts/edit/' . $post->id),
            'post'                     => $post,
            'postFormat'               => $post->post_format,
            'postImages'               => model('ImageModel')->findAllByPostId($post->id),
            'categorySelectorData'     => json_encode($categorySelectorData),
            'postSelections'           => model('PostSelectionModel')->findAllByPostId($post->id),
            'postVideo'                => !empty($post->video_id) ? model('MediaModel')->find($post->video_id) : null,
            'postAudios'               => $postAudios,
            'postItems'                => model('PostItemModel')->findAllByPostId($post->id, $post->post_format),
            'quizQuestions'            => $quizQuestions,
            'quizAnswers'              => $quizAnswers,
            'quizResults'              => $quizResults,
            'dataRecipe'               => $dataRecipe,
            'tagsStr'                  => $tagsStr,
            'attachments'              => $attachments,
            'attachmentIdsString'      => $attachmentIdsString,
            'panelSettings'            => panelSettings(),
            'showGenerateWithAi'       => $showGenerateWithAi,
            'breadcrumbLink'           => ['label' => trans("posts"), 'url' => adminUrl('posts')],
            'breadcrumbGenerateAiLink' => ($this->aiWriter->status && !empty($showGenerateWithAi)) ? true : false
        ]);
    }

    /**
     * Events
     */
    public function events()
    {
        checkPermission('add_post');

        if (!isPostFormatActive('event', $this->config)) {
            return redirect()->to(adminUrl());
        }

        return view('admin/post/event/events', [
            'title' => trans("events"),
        ]);
    }

    /**
     * Event Details
     */
    public function eventDetails($id)
    {
        checkPermission('add_post');

        if (!isPostFormatActive('event', $this->config)) {
            return redirect()->to(adminUrl());
        }

        $post = $this->postModel->find($id);
        if (empty($post)) {
            return redirect()->to(adminUrl('events'));
        }

        if (!checkPostOwnership($post->user_id)) {
            return redirect()->to(adminUrl('posts'));
        }

        $eventRegModel = model('EventRegistrationModel');

        if (isPostMethod()) {
            $submit = inputPost('submit');
            if ($submit === 'export') {
                if (!service('postService')->exportEventParticipantsCsv((int)$post->id)) {
                    return redirect()->back();
                }
            } else {
                $action = inputPost('action');
                if ($action === 'delete') {
                    $id = (int)inputPost('id');
                    $eventRegModel->delete($id) ? setSuccessMessage("msg_deleted") : setErrorMessage("msg_error");
                    return jsonResponse(true);
                }
            }
        }

        return view('admin/post/event/details', [
            'title'         => trans("events"),
            'action'        => adminUrl('events/event/' . $post->id),
            'panelSettings' => panelSettings(),
            'participants'  => $eventRegModel->findAllPaginated((int)$post->id, $this->perPage),
            'pager'         => $eventRegModel->pager,
            'event'         => $post
        ]);
    }

    /**
     * AJAX Endpoint: Get events by start and end date
     *
     * @method GET
     */
    public function getCalendarEvents()
    {
        if (!hasPermission('add_post')) {
            return jsonResponse(false);
        }

        if (!isPostFormatActive('event', $this->config)) {
            return redirect()->to(adminUrl());
        }

        $start = $this->request->getGet('start');
        $end = $this->request->getGet('end');

        if (!$start || !$end) {
            return $this->response->setJSON([]);
        }

        $userId = hasPermission('manage_all_posts') ? null : user()->id;

        $events = $this->postModel->getEventsByDateRange($start, $end, $userId);

        $calendarData = [];

        foreach ($events as $event) {
            $eventData = $event->post_data ?? null;
            $details = $eventData->details ?? null;

            $startDate = !empty($event->event_start_date) ? (string)$event->event_start_date : '';
            $endDate = !empty($event->event_end_date) ? (string)$event->event_end_date : '';

            $calendarData[] = [
                'id'            => $event->id,
                'title'         => $event->title,
                'start'         => $startDate,
                'end'           => $endDate,
                'className'     => "fc-event-primary",
                'extendedProps' => [
                    'description' => $event->summary,
                    'location'    => $details->venue_name ?? '--',
                    'details_url' => adminUrl('events/event/' . $event->id),
                    'edit_url'    => adminUrl('posts/edit/' . esc($event->id))
                ]
            ];
        }

        return $this->response->setJSON($calendarData);
    }

    /**
     * Helper: Handles post relations
     */
    private function handlePostRelations($post, $postData)
    {
        if (empty($post) || empty($postData)) {
            return;
        }

        $postService = service('postService');

        $postRelations = $postService->handlePostRelations($postData);

        // Update slug
        model('PageModel')->updateSlug('posts', $post->id);

        // Sync selections
        if (hasPermission('manage_all_posts')) {
            model('PostSelectionModel')->syncSelections($post->id, $post->lang_id, $postRelations->selections);
        }

        // Sync post images
        model('ImageModel')->syncImagesForPost($post->id, $postRelations->imageIdsCsv);

        // Sync audios
        model('MediaModel')->syncMediaForPost($post->id, $postRelations->audioIdsCsv, 'audio');

        // Sync tags
        model('TagModel')->syncTagsForPost($post, $postRelations->tags);

        // Sync attachments
        model('MediaModel')->syncMediaForPost($post->id, $postRelations->attachmentIdsCsv, 'attachment');

        // Sync post items
        $postItems = $postService->preparePostListItems($postData);
        model('PostItemModel')->syncItems($post->id, $postItems);

        // Sync quiz data
        if (in_array($post->post_format, ['trivia_quiz', 'personality_quiz', 'poll'])) {
            $quizService = new QuizService();
            $quizService->syncQuizData($postData, $post->id);
        }
    }

    /**
     * Handles Bulk and Single Post Actions
     */
    public function postAction()
    {
        $action = inputPost('action');
        $ids = filterValidIds(inputPost('ids'));

        if (empty($ids) || empty($action)) {
            setErrorMessage('msg_error');
            return jsonResponse(false);
        }

        $isSuccess = false;

        if ($action === 'delete') {
            $isSuccess = $this->postModel->deletePosts($ids);
        } elseif ($action === 'publish_draft') {
            $isSuccess = $this->postModel->publishPosts($ids, 'draft');
        } elseif ($action === 'publish_scheduled') {
            $isSuccess = $this->postModel->publishPosts($ids, 'scheduled');
        } else {
            // Check permissions for restricted actions
            if (!hasPermission('manage_all_posts')) {
                return jsonResponse(false);
            }

            if ($action === 'approve') {
                $isSuccess = $this->postModel->approvePosts($ids);
            } else {
                $parts = explode('_', $action, 2);

                if (count($parts) === 2) {
                    [$operation, $type] = $parts;
                    $allowedTypes = ['slider', 'featured', 'breaking_news', 'recommended'];

                    if (in_array($operation, ['add', 'remove']) && in_array($type, $allowedTypes)) {
                        $isSuccess = model('PostSelectionModel')->updatePostSelections($ids, $type, $operation);
                    }
                }
            }
        }

        if ($isSuccess) {
            clearContentCache();
            setSuccessMessage($action === 'delete' ? 'msg_deleted' : 'msg_updated');
            return jsonResponse(true);
        }

        setErrorMessage('msg_error');
        return jsonResponse(false);
    }

    /**
     * AJAX Endpoint: Update post order
     *
     * @method POST
     */
    public function updatePostOrder()
    {
        if (!hasPermission('manage_all_posts')) {
            return jsonResponse(false);
        }

        $postId = (int)inputPost('id');
        $type = inputPost('type');
        $order = (int)inputPost('order');

        if (model('PostModel')->updateOrder($postId, $type, $order)) {
            return jsonResponse(true);
        }

        return jsonResponse(false);
    }

    /**
     * AJAX Endpoint: Fetch video from URL
     *
     * @method POST
     */
    public function fetchVideoFromURL()
    {
        $url = inputPost('url');

        $fetcher = new \App\Libraries\VideoFetcher();
        $result = $fetcher->getInfo($url);

        if (!empty($result['success'])) {
            return jsonResponse($result);
        }

        return jsonResponse(false);
    }

    /**
     * AJAX Endpoint: Add new list item
     *
     * @method POST
     */
    public function addListItem()
    {
        $vars = [
            'postFormat' => inputPost('post_format'),
            'numItems'   => (int)inputPost('num_items')
        ];

        return jsonResponse([
            'result'      => 1,
            'htmlContent' => view('admin/post/_list_item', $vars)
        ]);
    }

    /**
     * AJAX Endpoint: Add new quiz item
     *
     * @method POST
     */
    public function addQuizItem()
    {
        $itemType = inputPost('item_type');

        if ($itemType === 'question') {
            $vars = [
                'postFormat' => inputPost('post_format'),
                'numItems'   => (int)inputPost('num_items'),
            ];
            $view = 'admin/post/quiz/_question';

        } elseif ($itemType === 'answer') {
            $vars = [
                'postFormat'   => inputPost('post_format'),
                'questionId'   => inputPost('question_id'),
                'answerFormat' => inputPost('answer_format'),
            ];
            $view = 'admin/post/quiz/_answer';

        } elseif ($itemType === 'result') {
            $vars = [
                'postFormat' => inputPost('post_format'),
                'numItems'   => (int)inputPost('num_items'),
            ];
            $view = 'admin/post/quiz/_result';

        } else {
            return jsonResponse(['result' => 0]);
        }

        return jsonResponse([
            'result'      => 1,
            'htmlContent' => view($view, $vars)
        ]);
    }

    /**
     * AJAX Endpoint: Generate AI Post
     *
     * @method POST
     */
    public function generateAiPost()
    {
        hasPermission("ai_writer");

        $val = \Config\Services::validation();
        $val->setRules([
            'topic'         => 'required|min_length[3]',
            'generate_type' => 'required',
            'lang_name'     => 'required',
        ]);

        if (!$val->withRequest($this->request)->run()) {
            return jsonResponse([
                'status'  => 'error',
                'message' => $val->getErrors()
            ]);
        }

        $options = (object)[
            'topic'        => inputPost('topic'),
            'langName'     => inputPost('lang_name'),
            'generateType' => inputPost('generate_type'),
            'tone'         => inputPost('tone'),
            'length'       => inputPost('length')
        ];

        try {
            $aiWriterLib = new AIWriter($this->config);
            $rawResponse = $aiWriterLib->write($options);

            $parsedData = $aiWriterLib->extractJson($rawResponse);

            if (empty($parsedData)) {
                throw new \Exception("Unable to parse the AI response or the format is invalid.");
            }

            if (empty($parsedData['content']) && empty($parsedData['title'])) {
                throw new \Exception(trans("ai_writer_api_error"));
            }

            $response = [
                'status'   => 'success',
                'title'    => $parsedData['title'] ?? '',
                'summary'  => $parsedData['summary'] ?? '',
                'content'  => $parsedData['content'] ?? '',
                'keywords' => ''
            ];

            if (isset($parsedData['keywords'])) {
                if (is_array($parsedData['keywords'])) {
                    $response['keywords'] = implode(', ', $parsedData['keywords']);
                } else {
                    $response['keywords'] = $parsedData['keywords'];
                }
            }

            return jsonResponse($response);

        } catch (\Exception $e) {
            return jsonResponse([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Bulk Post Upload
     */
    public function bulkPostUpload()
    {
        checkPermission('add_post');

        if (!isSuperAdmin() && $this->config->bulk_post_upload_for_authors != 1) {
            return redirect()->to(adminUrl());
        }

        $storage = $this->config->active_storage ?? 'local';
        $batchSize = ($storage !== 'local') ? 1 : 3;

        return view('admin/post/bulk/form', [
            'title'         => trans("bulk_post_upload"),
            'batchSize'     => $batchSize,
            'panelSettings' => panelSettings()
        ]);
    }

    /**
     * Init Bulk Post Import
     */
    public function initPostImport()
    {
        $rules = [
            'csv_file'       => [
                'uploaded[csv_file]',
                'ext_in[csv_file,csv]',
            ],
            'publish_status' => 'required|in_list[published,draft,scheduled]',
            'publish_date'   => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return jsonResponse([
                'status'  => 'error',
                'message' => implode('. ', $this->validator->getErrors())
            ]);
        }

        $file = $this->request->getFile('csv_file');
        if (!$file || !$file->isValid()) {
            return jsonResponse([
                'status'  => 'error2',
                'message' => 'Invalid file.'
            ]);
        }

        $uploadService = new \App\Services\UploadService($this->config);

        $tempFile = $uploadService->uploadTempFile($file, false, ['csv']);
        if (empty($tempFile['path']) || !file_exists($tempFile['path'])) {
            return jsonResponse([
                'status'  => 'error',
                'message' => 'Invalid.'
            ]);
        }

        $fullPath = $tempFile['path'];

        try {
            $importService = new \App\Services\PostImportService();
            $totalRows = $importService->analyzeFile($fullPath);

            return jsonResponse([
                'status'     => 'success',
                'file_path'  => $tempFile['relativePath'] ?? '',
                'total_rows' => $totalRows,
                'batch_size' => 20,
            ]);

        } catch (\Exception $e) {
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            return jsonResponse([
                'status'  => 'error',
                'message' => 'CSV Error: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Process Bulk Import Batch (AJAX)
     */
    public function processPostImportBatch()
    {
        $inputData = $this->request->getPost();

        $rawInputPath = $inputData['file_path'] ?? '';
        $offset = (int)($inputData['offset'] ?? 0);
        $limit = (int)($inputData['limit'] ?? 20);
        $publishStatus = $inputData['publish_status'] ?? 'draft';
        $publishDate = $inputData['publish_date'] ?? null;

        if (!hasPermission('add_post')) {
            return jsonResponse([
                'status'  => 'error',
                'message' => 'Invalid file name.'
            ]);
        }


        $allowedBaseDir = FCPATH . 'uploads/temp/';
        $cleanFilename = basename($rawInputPath);

        if (empty($cleanFilename) || $cleanFilename === '.' || $cleanFilename === '..') {
            return jsonResponse([
                'status'  => 'error',
                'message' => 'Invalid file name.'
            ]);
        }

        $secureFullPath = $allowedBaseDir . $cleanFilename;
        if (!is_file($secureFullPath)) {
            return jsonResponse([
                'status'  => 'error',
                'message' => 'Temporary file not found or expired.'
            ]);
        }

        try {
            $importService = new \App\Services\PostImportService();

            $userId = user()->id;

            $result = $importService->importBatch(
                $secureFullPath,
                $offset,
                $limit,
                $publishStatus,
                $publishDate,
                $userId
            );

            return jsonResponse([
                'status'    => 'success',
                'processed' => $result['processed'],
                'errors'    => $result['errors']
            ]);

        } catch (\Exception $e) {
            return jsonResponse([
                'status'  => 'error',
                'message' => 'System Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Download CSV File Post
     */
    public function downloadCsvFile()
    {
        $submit = inputPost('submit');

        if ($submit == 'csv_template') {
            return $this->response->download(FCPATH . 'assets/file/csv_template.csv', null);
        } elseif ($submit == 'csv_example') {
            return $this->response->download(FCPATH . 'assets/file/csv_example.csv', null);
        }

        return redirect()->to(adminUrl('bulk-post-upload'));
    }

}
