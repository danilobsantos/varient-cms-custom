<?php

namespace App\Controllers;

use App\Services\QuizService;

class AjaxController extends BaseController
{
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        if (!$this->request->isAJAX()) {
            return jsonResponse(['status' => 400, 'message' => 'Invalid Request!']);
        }
    }

    /**
     * Load Captcha
     */
    public function loadCaptcha()
    {
        return jsonResponse([
            'status' => 1,
            'html'   => view('common/_captcha')
        ]);
    }

    /**
     * Sort Menu Items
     */
    public function sortMenuItems()
    {
        checkPermission('navigation');

        $postData = $this->request->getPost();

        model('PageModel')->sortMenuItems($postData);

        return jsonResponse(true);
    }

    /**
     * Increment Post Page Views
     */
    public function incrementPostViews()
    {
        $postId = (int)inputPost('postId');
        $mouseMoveCount = (int)inputPost('mouseMoveCount');
        $scrollCount = (int)inputPost('scrollCount');

        if (empty($postId)) {
            return jsonResponse(false);
        }

        $verification = $this->config->human_verification;

        $status = (int)($verification->status ?? 0);

        if ($status) {
            $minMouse = (int)($verification->mouse ?? 0);
            $minScroll = (int)($verification->scroll ?? 0);

            if ($mouseMoveCount < $minMouse || $scrollCount < $minScroll) {
                return jsonResponse(false);
            }
        }

        service('postService')->incrementPostViews((int)$postId);

        return jsonResponse(true);
    }

    /**
     * Load Posts By Category ID
     */
    public function loadPostsByCategory()
    {
        $contentType = inputPost('content_type');
        $categoryId = (int)inputPost('category_id');
        $blockType = inputPost('block_type');
        $limit = (int)inputPost('limit');

        if ($limit < 1) {
            $limit = 15;
        }

        if (empty($categoryId)) {
            return jsonResponse(['status' => 0, 'message' => 'Invalid Category ID']);
        }

        $posts = model('PostModel')->findAllByCategoryId($categoryId, $limit);

        $htmlContent = '';

        if ($contentType === 'nav') {

            $htmlContent = view('themes/common/nav/_nav_posts', [
                'navItemPosts' => $posts,
                'numCols'      => $limit
            ]);

        } else {
            $allowedBlocks = ['block-1', 'block-2', 'block-3', 'block-4', 'block-6'];
            if (!in_array($blockType, $allowedBlocks)) {
                return jsonResponse(['status' => 0, 'message' => 'Invalid Block Type']);
            }

            $category = model('CategoryModel')->find($categoryId);
            if (!$category) {
                return jsonResponse(['status' => 0, 'message' => 'Category not found']);
            }

            $blockMap = [
                'block-1' => '_block_content_1',
                'block-2' => '_block_content_2',
                'block-3' => '_block_content_3',
                'block-4' => '_block_content_4',
                'block-6' => '_block_videos',
            ];

            $categoryWidgets = getCategoryWidgets($category->parent_id, $this->widgets, $this->adSpaces, $this->activeLang->id);

            $htmlContent = loadView('category/' . $blockMap[$blockType], [
                'category'        => $category,
                'categoryPosts'   => $posts,
                'categoryWidgets' => $categoryWidgets,
                'hasSidebar'      => !empty($categoryWidgets->hasWidgets) ? true : false
            ]);
        }

        $status = !empty($htmlContent) ? 1 : 0;

        return jsonResponse([
            'status'      => $status,
            'htmlContent' => $htmlContent
        ]);
    }

    /**
     * Handles request to load more posts (Latest & Search)
     */
    public function loadMorePosts()
    {
        $langId = (int)inputPost('lang_id');
        $page = (int)inputPost('page');
        $type = inputPost('type');
        $viewType = inputPost('view_type');

        if ($page < 1) {
            $page = 1;
        }

        // Set limits dynamically based on the request type
        $perPage = $this->postsPerPage;
        $offset = ($page - 1) * $perPage;

        $result = [];

        if ($type === 'latest') {
            $result = service('postService')->getLatestPosts($langId, $perPage, $offset);
        } elseif ($type === 'search') {
            $q = removeSpecialCharacters(trim((string)inputPost('q')));
            $result = model('PostModel')->getSearchPosts($q, $langId, $perPage, $offset);
        }

        if (empty($result) || empty($result['posts'])) {
            return jsonResponse(false);
        }

        $posts = $result['posts'];
        $hasMore = $result['hasMore'];

        $htmlContent = '';

        $divClass = '';
        if ($viewType !== 'horizontal') {
            $widgets = getCategoryWidgets(0, $this->widgets, $this->adSpaces, $langId);
            $divClass = $widgets->hasWidgets ? 'col-sm-12 col-md-6' : 'col-sm-12 col-md-4';
        }

        foreach ($posts as $post) {
            $vars = ['postItem' => $post, 'showLabel' => true];

            if ($viewType === 'horizontal') {
                $htmlContent .= loadView('post/_post_item_horizontal', $vars);
            } else {
                $htmlContent .= ' <div class="' . $divClass . '">' . loadView('post/_post_item', $vars) . '</div>';
            }
        }

        return jsonResponse([
            'status'      => 1,
            'htmlContent' => $htmlContent,
            'hasMore'     => $hasMore
        ]);
    }

    /**
     * Add Poll Vote
     */
    public function addPollVote()
    {
        $pollId = (int)inputPost('poll_id');
        $optionId = (int)inputPost('option_id');
        $userId = authCheck() ? (int)user()->id : null;

        if ($pollId <= 0 || $optionId <= 0) {
            return jsonResponse(false);
        }

        $pollModel = model('PollModel');
        $pollVoteModel = model('PollVoteModel');
        $pollOptionModel = model('PollOptionModel');

        $poll = $pollModel->find($pollId);

        if (empty($poll) || ($userId === null && $poll->vote_permission === 'registered')) {
            return jsonResponse(false);
        }

        // Option must belong to this poll
        if (!$pollOptionModel->optionBelongsToPoll($pollId, $optionId)) {
            return jsonResponse(false);
        }

        // Create vote
        if ($pollVoteModel->create($pollId, $optionId, $userId)) {
            return jsonResponse(true);
        }

        return jsonResponse(false);
    }

    /**
     * Check Trivia Quiz Answer
     */
    public function checkTriviaQuizAnswer()
    {
        $questionId = (int)inputPost('question_id');
        $answerId = (int)inputPost('answer_id');

        if (empty($questionId) || empty($answerId)) {
            return jsonResponse(['result' => 0]);
        }

        $answerModel = model('QuizAnswerModel');

        $selectedAnswer = $answerModel->find($answerId);
        if (!$selectedAnswer || $selectedAnswer->question_id != $questionId) {
            return jsonResponse(['result' => 0]);
        }

        // Determine correctness
        $isCorrect = ($selectedAnswer->is_correct == 1) ? 1 : 0;
        $correctAnswerId = 0;

        if ($isCorrect == 0) {
            $correctAnswer = $answerModel->findCorrectAnswer($questionId);
            if ($correctAnswer) {
                $correctAnswerId = $correctAnswer->id;
            }
        } else {
            $correctAnswerId = $answerId;
        }

        return jsonResponse([
            'result'            => 1,
            'is_correct'        => $isCorrect,
            'correct_answer_id' => $correctAnswerId
        ]);
    }

    /**
     * Get Quiz Result
     */
    public function getQuizResult()
    {
        $data = [
            'post_id'       => (int)inputPost('post_id'),
            'quiz_type'     => inputPost('quiz_type'),
            'correct_count' => (int)inputPost('correct_count'),
            'user_answers'  => inputPost('user_answers'),
        ];

        $quizService = new QuizService();
        $result = $quizService->prepareQuizResult($data);

        return jsonResponse($result);
    }

    /**
     * Add Post Poll Vote
     */
    public function addPostPollVote()
    {
        $data = [
            'question_id' => (int)inputPost('question_id'),
            'answer_id'   => (int)inputPost('answer_id')
        ];

        $quizService = new QuizService();
        $result = $quizService->addPostPollVote($data);

        return jsonResponse($result);
    }

    /**
     * Add or Remove Reading List Item
     */
    public function toggleReadingListItem()
    {
        if (!authCheck()) {
            return jsonResponse(false);
        }

        $postId = (int)inputPost('post_id');
        $userId = (int)user()->id;

        model('ReadingListModel')->togglePost($postId, $userId);

        return jsonResponse(true);
    }

    /**
     * Toggle user emoji reaction
     */
    public function toggleUserEmojiReaction()
    {
        $postId = (int)inputPost('post_id');
        $reactionKey = cleanStr(inputPost('reaction'));

        if (!$postId || !$reactionKey) {
            return jsonResponse(false);
        }

        $post = model('PostModel')->find($postId);

        if (!$post) {
            return jsonResponse(false);
        }

        $contentAccessStatus = getContentAccessStatus($post);

        if (!$contentAccessStatus->hasAccess) {
            return jsonResponse(false);
        }

        $allowedReactions = getAppDefault('emojiReactions');
        $reactionKeys = array_column($allowedReactions, 'key');

        if (!in_array($reactionKey, $reactionKeys, true)) {
            return jsonResponse(false);
        }

        $model = model('ReactionModel');
        $operation = hasUserReacted($reactionKey, $post->id) ? 'decrease' : 'increase';
        $reaction = $model->findReactionByKey($reactionKey, $post->id);
        $dbSuccess = false;

        if ($operation === 'increase') {
            if (!$reaction) {
                $newId = $model->insert([
                    'post_id'  => $post->id,
                    'reaction' => $reactionKey,
                    'total'    => 1
                ]);

                if ($newId) {
                    $dbSuccess = true;
                    $reaction = (object)['id' => $newId, 'post_id' => $post->id, 'reaction' => $reactionKey, 'total' => 1];
                }
            } else {
                if ($model->updateReactionTotal($reaction->id, 'increase')) {
                    $dbSuccess = true;
                }
            }
        } elseif ($operation === 'decrease') {
            if ($reaction) {
                if ($model->updateReactionTotal($reaction->id, 'decrease')) {
                    $dbSuccess = true;
                }
            } else {
                return jsonResponse(false);
            }
        }

        if (!$dbSuccess) {
            return jsonResponse(false);
        }

        if ($operation === 'increase') {
            setSession(reactionSessionKey($reactionKey, $post->id), 1);
        } else {
            removeSession(reactionSessionKey($reactionKey, $post->id));
        }

        return jsonResponse(true);
    }

    /**
     * Handles the event registration form submission
     */
    public function registerEvent()
    {
        // Basic Request Validation
        $rules = [
            'post_id'    => 'required|numeric',
            'full_name' => 'required|min_length[2]|max_length[255]',
            'email'      => 'required|valid_email|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            return $this->response->setJSON(['status' => 0, 'message' => reset($errors)]);
        }

        // Get input data
        $postId = (int)inputPost('post_id');
        $formData = [
            'full_name'    => inputPost('full_name'),
            'email'         => inputPost('email'),
            'custom_data'   => $this->request->getPost('custom_data'),
            'custom_labels' => $this->request->getPost('custom_labels'),
        ];

        $result = service('postService')->registerForEvent($postId, $formData, authCheck() ? user()->id : null);

        return $this->response->setJSON($result);
    }

    /*
    * --------------------------------------------------------------------
    * Newsletter
    * --------------------------------------------------------------------
    */

    /**
     * Add newsletter email
     */
    public function addNewsletterEmail()
    {
        $url = inputPost('url');
        if (!empty($url)) {
            return jsonResponse(false);
        }

        $email = trim(inputPost('email') ?? '');
        $response = [
            'status'  => 0,
            'message' => trans("msg_newsletter_error"),
        ];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = trans("message_invalid_email");
            return jsonResponse($response);
        }

        $model = model('NewsletterModel');

        $exists = $model->where('email', cleanStr($email))->countAllResults();

        if ($exists > 0) {
            $response['message'] = trans("msg_newsletter_error");
            return jsonResponse($response);
        }

        if ($model->create($email)) {
            $response['status'] = 1;
            $response['message'] = trans("msg_newsletter_success");
            return jsonResponse($response);
        }

        return jsonResponse($response);
    }

    /**
     * Load More Users
     */
    public function loadMoreUsers()
    {
        checkPermission('newsletter');

        $page = max(1, (int)inputPost('page'));
        $q = inputPost('q') ?? '';
        $perPage = 500;
        $offset = ($page - 1) * $perPage;

        $users = model('UserModel')->loadMoreUsers($q, $perPage, $offset);

        $htmlContent = '';

        if (!empty($users)) {
            foreach ($users as $user) {
                $htmlContent .= '
                <tr>
                    <td class="w-10px">
                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="id[]" value="' . $user->id . '" data-email="' . esc($user->email) . '">
                        </div>
                    </td>
                    <td class="min-w-20px">' . $user->id . '</td>
                    <td class="min-w-125px">' . esc($user->username) . '</td>
                    <td class="min-w-125px">' . esc($user->email) . '</td>
                    <td class="min-w-125px">' . esc($user->created_at) . '</td>
                </tr>';
            }
        } else {
            if ($page == 1) {
                $htmlContent .= '<tr><td colspan="5"><p class="text-muted text-center">' . trans("no_records_found") . '</p></td></tr>';
            }
        }

        return jsonResponse([
            'result'      => 1,
            'htmlContent' => $htmlContent,
            'count'       => count($users)
        ]);
    }

    /**
     * Load More Newsletter Emails
     */
    public function loadMoreNewsletterEmails()
    {
        checkPermission('newsletter');

        $page = max(1, (int)inputPost('page'));
        $q = inputPost('q') ?? '';
        $perPage = 500;
        $offset = ($page - 1) * $perPage;

        $rows = model('NewsletterModel')->loadMore($q, $perPage, $offset);

        $htmlContent = '';

        if (!empty($rows)) {
            foreach ($rows as $row) {
                $htmlContent .= '
                    <tr id="rowNewsletter' . $row->id . '">
                        <td>
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="id[]" value="' . $row->id . '" data-email="' . esc($row->email) . '">
                            </div>
                        </td>
                     <td class="min-w-20px">' . $row->id . '</td>
                     <td class="min-w-125px">' . esc($row->email) . '</td>
                     <td class="min-w-125px">' . esc($row->created_at) . '</td>
                        <td class="text-end">
                            <a href="javascript:void(0)" onclick="deleteNewsletterEmail(' . $row->id . ');" class="btn btn-sm btn-light-danger justify-content-center align-items-center p-0 w-40px h-40px">
                                <i class="ki-duotone ki-trash m-0 p-0 fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                            </a>
                        </td>
                    </tr>';
            }
        } else {
            if ($page == 1) {
                $htmlContent .= '<tr><td colspan="5"><p class="text-muted text-center">' . trans("no_records_found") . '</p></td></tr>';
            }
        }

        return jsonResponse([
            'result'      => 1,
            'htmlContent' => $htmlContent,
            'count'       => count($rows)
        ]);
    }

    /**
     * Get Tag Suggestions
     */
    public function getTagSuggestions()
    {
        hasPermission("add_post");

        $q = inputPost('searchTerm');
        $limit = 20;

        $tags = model('TagModel')->getTagSuggestions($q, (int)$this->activeLang->id, $limit);
        if (!empty($tags)) {
            $data = [
                'result' => 1,
                'tags'   => $tags
            ];
            return jsonResponse($data);
        }

        return jsonResponse(['result' => 0]);
    }

    /**
     * Delete dropzoneJS Uploaded File
     */
    public function deleteDropzoneUploadedFile()
    {
        $fileDataJson = inputPost('file_data');

        $fileData = json_decode($fileDataJson, true);
        if (empty($fileData) || !is_array($fileData)) {
            return jsonResponse(false);
        }

        $storage = $fileData['storage'] ?? 'local';
        unset($fileData['storage']);

        if (!empty($fileData)) {
            foreach ($fileData as $path) {
                deleteStorageFile($path, $storage);
            }
        }

        return jsonResponse(true);
    }

    /**
     * Load email template preview
     */
    public function loadEmailTemplatePreview()
    {
        checkPermission('settings');

        $template = inputPost('template') ?: 'pure_minimalist';
        $viewPath = "email/" . $template;

        $data = [
            'subject'       => 'Verify Your Email',
            'bodyText'      => "Hi there, <br><br>Thanks for getting started with us! To finish your registration and ensure your account's security, please verify your email address by clicking the button below.",
            'buttonText'    => 'Verify Email',
            'buttonUrl'     => base_url('verify-email'),
            'token'         => '',
            'templateColor' => $this->activeTheme->theme_color
        ];

        $settings = getContextValue('settings');
        $data['socialLinks'] = getSiteSocialLinks($settings);
        $rawHtml = view($viewPath, $data);

        $processor = new \App\Libraries\EmailProcessor();
        $html = $processor->inline($rawHtml);

        return jsonResponse([
            'status'      => 1,
            'htmlContent' => $html
        ]);
    }
}
