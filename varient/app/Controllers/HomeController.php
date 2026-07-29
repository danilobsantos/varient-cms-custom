<?php

namespace App\Controllers;

use App\Services\CaptchaService;
use App\Services\EmailService;
use App\Services\JsonLdService;

class HomeController extends BaseController
{

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    /**
     * Index Page
     */
    public function index()
    {
        $showcasePostsLimit = defined('LIMIT_SHOWCASE_POSTS') ? (int)LIMIT_SHOWCASE_POSTS : 50;

        // Latest posts
        $latestPostsResult = service('postService')->getLatestPosts($this->activeLang->id, $showcasePostsLimit, 0);
        $latestPosts = $latestPostsResult['posts'] ?? [];

        // Slider posts
        $sliderPosts = service('postService')->getSliderPosts($this->showcasePosts, $latestPosts);

        // Extract the IDs of the slider posts to exclude them from the featured section
        $sliderPostIds = array_column($sliderPosts, 'id');

        // Featured posts
        $featuredPosts = service('postService')->getFeaturedPosts($this->showcasePosts, $latestPosts, $sliderPostIds);

        // Breaking News
        $breakingNews = service('postService')->getBreakingNews($this->showcasePosts, $latestPosts);

        $data = [
            'title'         => $this->settings->home_title,
            'description'   => $this->settings->site_description,
            'keywords'      => $this->settings->keywords,
            'homeTitle'     => $this->settings->home_title,
            'userSession'   => getUserSession(),
            'latestPosts'   => $latestPosts,
            'sliderPosts'   => $sliderPosts,
            'featuredPosts' => $featuredPosts,
            'breakingNews'  => $breakingNews,
            'bodyClass'     => 'body-index-page'
        ];

        // JSON-LD
        $jsonLdService = new JsonLdService();
        $typesToGenerate = ['website', 'organization'];
        $data['jsonLdScript'] = $jsonLdService->generate($typesToGenerate, $data);

        return loadView('index', $data);
    }

    /**
     * Posts Page
     */
    public function posts()
    {
        $postModel = model('PostModel');

        $data = [
            'pageType'      => 'posts',
            'posts'         => $postModel->findAllPaginated((int)$this->activeLang->id, [], (int)$this->postsPerPage),
            'pager'         => $postModel->pager,
            'userSession'   => getUserSession(),
            'mainPostsPage' => true
        ];

        $data = setPageMeta(trans("posts"), $data);

        return loadView('post/posts', $data);
    }

    /**
     * Handles dynamic URLs resolved by slug
     */
    public function any($slug)
    {
        $slug = cleanSlug($slug ?? '');
        if (empty($slug)) {
            return $this->error404();
        }

        $page = model('PageModel')->findBySlug($slug, (int)$this->activeLang->id);
        if (!empty($page)) {
            return $this->page($page);
        }

        $category = model('CategoryModel')->findBySlug($slug, (int)$this->activeLang->id);
        if (!empty($category)) {
            return $this->category($category);
        }

        $post = model('PostModel')->findBySlug($slug, (int)$this->activeLang->id);
        if (!empty($post)) {
            return $this->post($post);
        }

        return $this->error404();
    }

    /**
     * Post Page
     */
    private function post($post)
    {
        if (empty($post)) {
            return $this->error404();
        }

        if (!authCheck() && (int)$post->need_auth === 1) {
            setErrorMessage("message_post_auth");
            return redirect()->to(generateURL('sign_up'));
        }

        $postModel = model('PostModel');

        $postId = (int)$post->id;
        $categoryId = (int)$post->category_id;
        $postFormat = (string)$post->post_format;

        $postTags = model('TagModel')->findAllByPostId($postId);

        $data = [
            'title'           => !empty($post->meta_title) ? $post->meta_title : $post->title,
            'description'     => !empty($post->meta_description) ? $post->meta_description : $post->summary,
            'keywords'        => $post->meta_keywords,
            'ogTitle'         => $post->title,
            'ogType'          => 'article',
            'ogImage'         => getPostImageUrl($post, 'big'),
            'ogWidth'         => '870',
            'ogHeight'        => '580',
            'ogCreator'       => $post->author_username,
            'ogAuthor'        => $post->author_username,
            'ogPublishedTime' => $post->created_at,
            'ogModifiedTime'  => !empty($post->updated_at) ? $post->updated_at : $post->created_at,
            'ogTags'          => $postTags,
            'userSession'     => getUserSession(),
            'post'            => $post,
            'postUser'        => model('UserModel')->find((int)$post->user_id),
            'postTags'        => $postTags,
            'postImages'      => model('ImageModel')->findAllByPostId($postId),
            'postVideo'       => !empty($post->video_id) ? model('MediaModel')->find($post->video_id) : null,
            'relatedPosts'    => $postModel->getRelatedPosts($postId, $categoryId),
            'breadcrumb'      => model('CategoryModel')->getBreadcrumb($categoryId),
            'postFormat'      => $postFormat,
            'feed'            => !empty($post->feed_id) ? model('RssModel')->find((int)$post->feed_id) : null,
            'postReactions'   => model('ReactionModel')->findAllByPostId($postId, true),
            'isFullWidth'     => (int)$post->full_width_post === 1,
            'isPostPage'      => 1
        ];

        // Premium Check
        $premiumAccessStatus = getContentAccessStatus($post);

        $data['hasAccess'] = $premiumAccessStatus->hasAccess;
        $data['restrictionType'] = $premiumAccessStatus->restrictionType;
        $paywallAppearance = $this->premiumMembership->paywallAppearance ?? '';

        $postItemModel = model('PostItemModel');
        $postListItems = [];
        if (in_array($postFormat, ['gallery', 'sorted_list', 'table_of_contents', 'recipe'], true)) {
            $postListItems = $postItemModel->findAllByPostId($postId, $postFormat);
        }

        $data['postListItems'] = $postListItems;

        // Gallery post
        if ($postFormat === 'gallery') {

            $itemCount = count($postListItems);
            $pageNum = max((int)inputGet('p'), 1);
            $pageNum = min($pageNum, $itemCount);

            $data['galleryPostItem'] = $postItemModel->findByPageNumber($postId, $pageNum);
            $data['galleryPostNumRows'] = $itemCount;
            $data['pageNumber'] = $pageNum;

            // Check premium access for other pages of the gallery post
            if ($pageNum > 1 && !$premiumAccessStatus->hasAccess) {
                return redirect()->to(generatePostUrl($post));
            }
        }

        // Audio post
        if ($postFormat === 'audio') {
            $data['postAudios'] = model('MediaModel')->findAllMediaByPostId($post->id, 'audio');
        }

        // Quiz post
        if (in_array($postFormat, ['trivia_quiz', 'personality_quiz', 'poll'])) {

            $quizQuestionModel = model('QuizQuestionModel');
            $quizAnswerModel = model('QuizAnswerModel');

            $data['quizQuestions'] = $quizQuestionModel->findAllByPostId($postId);
            $data['quizAnswers'] = $quizAnswerModel->findAllByPostId($postId, true);

            if ($postFormat === 'poll') {
                $data['userPollAnswers'] = authCheck() ? $quizAnswerModel->findAllPollVotesByUserId($postId, (int)user()->id) : [];
            }
        }

        // Event post
        if ($postFormat === 'event') {
            $isUserRegistered = false;
            if (authCheck()) {
                $isUserRegistered = model('EventRegistrationModel')->isRegistered($postId, (int)user()->id);
            }
            $data['isUserRegistered'] = $isUserRegistered;
            $data['registrationCount'] = model('EventRegistrationModel')->getRegistrationCount($postId);
        }

        // Attachments
        $data['attachments'] = model('MediaModel')->findAllMediaByPostId($postId, 'attachment');

        $data['isInReadingList'] = false;
        if (authCheck()) {
            $data['isInReadingList'] = model('ReadingListModel')->isPostInList($postId, (int)user()->id);
        }

        // Next & Prev Posts
        $data['adjacentPosts'] = $postModel->getAdjacentPosts($postId, $post->lang_id);

        // Time spent limit
        $data['postTimeSpent'] = 0;
        if ((int)($this->config->human_verification->status ?? 0) === 1) {
            $timeSpent = max((int)($this->config->human_verification->time_spent ?? 0), 0);
            $data['postTimeSpent'] = $timeSpent * 1000;
        }

        // Post comments
        $commentModel = model("CommentModel");
        $ownedCommentIds = getSession('owned_comment_ids') ?? [];
        $likedCommentIds = getSession('liked_comment_ids') ?? [];
        $limit = 5;

        $data['comments'] = $commentModel->getCommentsWithReplies((int)$post->id, $limit, 0, 'newest', $ownedCommentIds, $likedCommentIds);
        $data['commentCounts'] = $commentModel->getPostCommentCounts((int)$post->id);

        // JSON-LD
        $jsonLdService = new JsonLdService();
        $typesToGenerate = ['breadcrumb'];
        if ($postFormat === 'recipe') {
            $typesToGenerate[] = 'recipe';
        } elseif ($postFormat === 'event') {
            $typesToGenerate[] = 'event';
        } else {
            $typesToGenerate[] = 'news_article';
        }

        $data['jsonLdScript'] = $jsonLdService->generate($typesToGenerate, $data);

        $view = !$premiumAccessStatus->hasAccess && $paywallAppearance === 'strict' ? 'post_strict' : 'post';
        return loadView('post/' . $view, $data);
    }

    /**
     * Page
     */
    private function page($page)
    {
        if (empty($page) || (int)$page->status === 0) {
            return $this->error404();
        }

        if (!authCheck() && (int)$page->need_auth === 1) {
            setErrorMessage("message_page_auth");
            return redirect()->to(generateURL('sign_up'));
        }

        $data = [
            'title'       => $page->meta_title ?: $page->title,
            'description' => $page->description,
            'keywords'    => $page->keywords,
            'userSession' => getUserSession(),
            'page'        => $page,
        ];

        // Gallery page
        if ($page->page_default_name === 'gallery') {

            $data['albums'] = model('GalleryAlbumModel')->findAllPaginated(['lang_id' => (int)$this->activeLang->id], 24);

            return loadView('gallery/gallery', $data);
        }

        // Contact page
        if ($page->page_default_name === 'contact') {

            $data['termsConditions'] = model('PageModel')->findByDefaultName('terms_conditions', (int)$this->activeLang->id);

            return loadView('contact', $data);
        }

        return loadView('page', $data);
    }

    /**
     * Category Page
     */
    private function category($category, $isParent = true)
    {
        if (empty($category)) {
            return $this->error404();
        }

        $parentId = (int)$category->parent_id;

        if ($isParent && $parentId !== 0) {
            return $this->error404();
        }

        $categoryModel = model('CategoryModel');

        $subCategories = $categoryModel->findAllByParentId((int)$category->id, true);
        $hasSubCategories = !empty($subCategories) && countItems($subCategories) > 0 ? true : false;
        $parentCategory = $parentId !== 0 ? $categoryModel->findById($parentId, true) : null;

        $data = [
            'pageType'         => 'category',
            'title'            => !empty($category->meta_title) ? $category->meta_title : $category->name,
            'pageTitle'        => $category->name,
            'description'      => $category->description,
            'keywords'         => $category->keywords,
            'category'         => $category,
            'subCategories'    => $subCategories,
            'hasSubCategories' => $hasSubCategories,
            'parentCategory'   => $parentCategory,
            'posts'            => model('PostModel')->findAllPaginated((int)$this->activeLang->id, ['category' => $category], (int)$this->postsPerPage),
            'pager'            => model('PostModel')->pager
        ];

        // JSON-LD
        $jsonLdService = new JsonLdService();
        $typesToGenerate = ['breadcrumb'];
        $data['jsonLdScript'] = $jsonLdService->generate($typesToGenerate, $data);

        return loadView('post/posts', $data);
    }

    /**
     * Subcategory Page
     */
    public function subCategory($parentSlug, $slug)
    {
        $parentSlug = cleanSlug($parentSlug ?? '');
        $slug = cleanSlug($slug ?? '');

        $categoryModel = model('CategoryModel');

        $categoryParent = $categoryModel->findBySlug($parentSlug, (int)$this->activeLang->id);

        $category = $categoryModel->findBySlug($slug, (int)$this->activeLang->id);

        if (empty($categoryParent) || empty($category)) {
            return $this->error404();
        }

        return $this->category($category, false);
    }

    /**
     * Tag Page
     */
    public function tag($slug)
    {
        $slug = cleanSlug($slug);

        $tag = model('TagModel')->findBySlug((string)$slug, (int)$this->activeLang->id);
        if (empty($tag)) {
            return $this->error404();
        }

        $data = [
            'pageType'    => 'tag',
            'tag'         => $tag,
            'userSession' => getUserSession(),
            'posts'       => model('PostModel')->findAllPaginated((int)$this->activeLang->id, ['tag_id' => $tag->id], (int)$this->postsPerPage),
            'pager'       => model('PostModel')->pager
        ];

        $data = setPageMeta($data['tag']->tag, $data);

        $data['title'] = trans("tag") . ': ' . $tag->tag;
        $data['robots'] = 'noindex, follow';

        return loadView('post/posts', $data);
    }

    /**
     * Gallery Album Page
     */
    public function galleryAlbum($id)
    {
        $page = model('PageModel')->findByDefaultName('gallery', (int)$this->activeLang->id);
        if (empty($page) || !$page->status) {
            return $this->error404();
        }

        $album = model('GalleryAlbumModel')->find($id);
        if (empty($album)) {
            return $this->error404();
        }

        return loadView('gallery/album', [
            'title'       => $page->title,
            'description' => $page->description,
            'keywords'    => $page->keywords,
            'jsPage'      => 'gallery',
            'userSession' => getUserSession(),
            'album'       => $album,
            'page'        => $page,
            'categories'  => model('GalleryCategoryModel')->findAllByAlbum($album->id),
            'images'      => model('GalleryImageModel')->findAllByAlbum($album->id)
        ]);
    }

    /**
     * Reading List Page
     */
    public function readingList()
    {
        if (!authCheck()) {
            return redirect()->to(langBaseUrl());
        }

        $data = [
            'pageType'    => 'reading_list',
            'userSession' => getUserSession(),
            'posts'       => model('PostModel')->findAllPaginated((int)$this->activeLang->id, ['rd_list_user_id' => user()->id], (int)$this->postsPerPage),
            'pager'       => model('PostModel')->pager
        ];

        $data = setPageMeta(trans("reading_list"), $data);

        return loadView('post/posts', $data);
    }

    /**
     * Search Page
     */
    public function search()
    {
        $q = removeSpecialCharacters(trim(inputGet('q') ?? ''));
        if (empty($q)) {
            return redirect()->to(langBaseUrl());
        }

        $result = model('PostModel')->getSearchPosts((string)$q, (int)$this->activeLang->id, (int)$this->postsPerPage, 0);

        $data = [
            'pageType'    => 'search',
            'q'           => $q,
            'userSession' => getUserSession(),
            'posts'       => $result['posts'] ?? [],
            'hasMore'     => $result['hasMore'] ?? false,
        ];

        $data = setPageMeta(trans("search") . ': ' . $q, $data);
        $data['robots'] = 'noindex, follow';

        return loadView('post/posts', $data);
    }

    /**
     * Contact Page Post
     */
    public function contactPost()
    {
        $captchaService = new CaptchaService($this->config);
        if (!$captchaService->verify()) {
            setErrorMessage("bot_verification_failed");
            return redirect()->to(generateURL('contact'))->withInput();
        }

        $postData = $this->request->getPost();

        // Security: Check email blacklisted
        $email = $postData['email'] ?? '';
        if (model('EmailBlacklistModel')->isBlacklisted($email)) {
            setErrorMessage("msg_error_email_blacklisted");
            return redirect()->to(generateURL('contact'))->withInput();
        }

        $model = model('ContactModel');

        if ($model->create($postData)) {

            // Send email
            if ((int)$this->config->mail_contact_status === 1 && !empty($this->config->mail_contact)) {
                $emailService = new EmailService();
                $emailService->sendContactNotificationEmail($postData);
            }

            setSuccessMessage("message_contact_success");
            return redirect()->to(generateURL('contact'));
        } else {
            setErrorMessage("message_contact_error");
            return redirect()->to(generateURL('contact'))->withInput();
        }
    }

    /**
     * Post Preview Page
     */
    public function preview($slug)
    {
        $slug = cleanSlug($slug);

        if (!authCheck() || empty($slug)) {
            return redirect()->to(langBaseUrl());
        }

        $post = model('PostModel')->findPreviewBySlug($slug);

        if (!empty($post)) {

            if (!checkPostOwnership($post->user_id)) {
                return redirect()->to(langBaseUrl());
            }

            return $this->post($post);

        } else {
            return $this->error404();
        }
    }

    /**
     * Handles file downloads for various media types and storage drivers
     */
    public function downloadFile()
    {
        $id = (int)inputPost('id');
        $requestedFileType = inputPost('file_type');

        $allowedTypes = ['attachment', 'audio'];
        if (!$id || !in_array($requestedFileType, $allowedTypes, true)) {
            return redirect()->back();
        }

        $mediaModel = model('MediaModel');
        $media = $mediaModel->findById($id);

        if (empty($media) || $media->selection_type !== $requestedFileType) {
            return redirect()->back();
        }

        $storage = $media->storage ?? 'local';
        $filePath = $media->file_path;
        $fileName = $media->file_name;

        if ($storage === 'local') {
            $absolutePath = FCPATH . $filePath;

            if (!is_file($absolutePath)) {
                return redirect()->back();
            }

            return $this->response->download($absolutePath, null)->setFileName($fileName);
        }

        try {
            $storageService = service('storage');
            $response = $storageService->downloadFile($filePath, $fileName, $storage);

            if ($response) {
                return $response;
            } else {
                return redirect()->back()->with('error', 'File could not be retrieved.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Remote download failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'System error during download.');
        }
    }

    /**
     * Newsletter Remove
     */
    public function newsletterRemove()
    {
        $token = cleanStr(inputGet('token') ?? '');
        if (empty($token)) {
            return redirect()->to(langBaseUrl());
        }

        $model = model('NewsletterModel');
        $email = $model->findByToken($token);

        if (!empty($email)) {

            $model->delete($email->id);

            $data = setPageMeta(trans("unsubscribe"));
            $data['pageType'] = 'newsletter_remove';

            return loadCommonView('auth/verification', $data);
        }

        return redirect()->to(langBaseUrl());
    }
}
