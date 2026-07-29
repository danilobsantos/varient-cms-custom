<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$languages = getContextValue('languages');
$config = getContextValue('config');
$customRoutes = getContextValue('customRoutes');

$routes->set404Override('App\Controllers\HomeController::error404');

$routes->match(['GET', 'HEAD'], '/', 'HomeController::index');

$routes->get('manifest.json', 'FeedController::manifest');
$routes->get('newsletter/unsubscribe', 'HomeController::newsletterRemove');
$routes->match(['GET', 'HEAD'], 'sitemap-news.xml', 'FeedController::googleNewsSitemap');

/*
 * --------------------------------------------------------------------
 * RESTful API Routes (v1)
 * --------------------------------------------------------------------
 */

$routes->group('api/v1', ['namespace' => 'App\\Controllers\\Api'], function ($routes) {
    
    // Authentication
    $routes->post('auth/login', 'AuthController::login');
    $routes->post('auth/register', 'AuthController::register');
    $routes->post('auth/refresh', 'AuthController::refresh');
    $routes->post('auth/logout', 'AuthController::logout');
    $routes->get('auth/me', 'AuthController::me');
    
    // Posts Resource
    $routes->get('posts', 'PostsController::index');
    $routes->get('posts/(:num)', 'PostsController::show/$1');
    $routes->post('posts', 'PostsController::store');
    $routes->put('posts/(:num)', 'PostsController::update/$1');
    $routes->patch('posts/(:num)', 'PostsController::patch/$1');
    $routes->delete('posts/(:num)', 'PostsController::destroy/$1');
    
    // Categories Resource
    $routes->get('categories', 'CategoriesController::index');
    $routes->get('categories/(:num)', 'CategoriesController::show/$1');
    $routes->post('categories', 'CategoriesController::store');
    $routes->put('categories/(:num)', 'CategoriesController::update/$1');
    $routes->delete('categories/(:num)', 'CategoriesController::destroy/$1');
    
    // Users Resource
    $routes->get('users', 'UsersController::index');
    $routes->get('users/(:num)', 'UsersController::show/$1');
    $routes->post('users', 'UsersController::store');
    $routes->put('users/(:num)', 'UsersController::update/$1');
    $routes->delete('users/(:num)', 'UsersController::destroy/$1');
    
    // Comments Resource (Nested under Posts)
    $routes->get('posts/(:num)/comments', 'CommentsController::index/$1');
    $routes->post('posts/(:num)/comments', 'CommentsController::store/$1');
    $routes->delete('comments/(:num)', 'CommentsController::destroy/$1');
});

/*
 * --------------------------------------------------------------------
 * Service Routes
 * --------------------------------------------------------------------
 */

$routes->group('service', function ($routes) {
    // Google News
    $routes->match(['GET', 'HEAD'], 'feed/google-news', 'FeedController::googleNewsRss');
    $routes->match(['GET', 'HEAD'], 'feed/google-news/category/(:num)', 'FeedController::googleNewsRss/$1');

    // Cron
    $routes->get('cron/main', 'CronController::run');
    $routes->get('cron/subscription', 'CronController::run/subscription');
});

// Payment Webhook
$routes->group('webhook', function ($routes) {
    $routes->post('paypal', 'WebhookController::paypal');
    $routes->post('stripe', 'WebhookController::stripe');
    $routes->post('razorpay', 'WebhookController::razorpay');
    $routes->post('iyzico', 'WebhookController::iyzico');
    $routes->post('mercadopago', 'WebhookController::mercadopago');
    $routes->post('paytabs', 'WebhookController::paytabs');
});

// Checkout
$routes->group('checkout/payment', function ($routes) {
    $routes->post('paypal', 'PaymentController::paypal');
    $routes->get('stripe', 'PaymentController::stripe');
    $routes->post('razorpay', 'PaymentController::razorpay');
    $routes->get('mercadopago', 'PaymentController::mercadopago');
    $routes->match(['GET', 'POST'], 'iyzico', 'PaymentController::iyzico');
    $routes->match(['GET', 'POST'], 'paytabs', 'PaymentController::paytabs');
});

/*
 * --------------------------------------------------------------------
 * Auth Routes
 * --------------------------------------------------------------------
 */

$routes->group('auth', function ($routes) {
    $routes->post('login', 'AuthController::login');
    $routes->post('sign-up', 'AuthController::signUp');
    $routes->post('forgot-password', 'AuthController::forgotPassword');
    $routes->post('reset-password', 'AuthController::resetPassword');
    $routes->get('login/google', 'AuthController::initGoogleLogin');
    $routes->get('google/callback', 'AuthController::handleGoogleCallback');
    $routes->post('logout', 'AuthController::logout');
    $routes->get('login-google', 'AuthController::initGoogleLogin');
    $routes->get('google/callback', 'AuthController::handleGoogleCallback');
});

/*
 * --------------------------------------------------------------------
 * Post Method Routes
 * --------------------------------------------------------------------
 */

$routes->post('contact-post', 'HomeController::contactPost');
$routes->post('register-event-post', 'AjaxController::registerEvent');
$routes->post('toggle-follow-post', 'AccountController::toggleFollowPost');
$routes->post('account-settings-post', 'AccountController::accountSettingsPost');
$routes->post('download-file', 'HomeController::downloadFile');

/*
 * --------------------------------------------------------------------
 * Admin Routes
 * --------------------------------------------------------------------
 */

// Login
$routes->match(['GET', 'POST'], $customRoutes->admin . '/login', 'AuthController::login');

$routes->group($customRoutes->admin, ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'AdminController::index');
    $routes->match(['GET', 'POST'], 'themes', 'AdminController::themes');

    // Pages
    $routes->get('pages', 'AdminController::pages');
    $routes->match(['GET', 'POST'], 'pages/add', 'AdminController::addPage');
    $routes->match(['GET', 'POST'], 'pages/edit/(:num)', 'AdminController::editPage/$1');

    // Navigation
    $routes->get('navigation', 'AdminController::navigation');
    $routes->match(['GET', 'POST'], 'navigation/add', 'AdminController::addMenuLink');
    $routes->match(['GET', 'POST'], 'navigation/edit/(:num)', 'AdminController::editMenuLink/$1');

    // Posts
    $routes->get('add-post/format', 'PostController::postFormat');
    $routes->match(['GET', 'POST'], 'add-post', 'PostController::addPost');
    $routes->match(['GET', 'POST'], 'posts/edit/(:num)', 'PostController::editPost/$1');
    $routes->get('posts', 'PostController::posts');
    $routes->get('pending-posts', 'PostController::posts/pending');
    $routes->get('scheduled-posts', 'PostController::posts/scheduled');
    $routes->get('drafts', 'PostController::posts/draft');
    $routes->get('bulk-post-upload', 'PostController::bulkPostUpload');

    // Events
    $routes->get('events', 'PostController::events');
    $routes->get('events/feed', 'PostController::getCalendarEvents');
    $routes->match(['GET', 'POST'], 'events/event/(:num)', 'PostController::eventDetails/$1');

    //RSS feeds
    $routes->get('rss-feeds', 'RssController::rssFeeds');
    $routes->match(['GET', 'POST'], 'rss-feeds/add', 'RssController::addFeed');
    $routes->match(['GET', 'POST'], 'rss-feeds/edit/(:num)', 'RssController::editFeed/$1');

    // Categories
    $routes->get('categories', 'CategoryController::categories');
    $routes->match(['GET', 'POST'], 'categories/add', 'CategoryController::addCategory');
    $routes->match(['GET', 'POST'], 'categories/edit/(:num)', 'CategoryController::editCategory/$1');

    // Widgets
    $routes->get('widgets', 'AdminController::widgets');
    $routes->match(['GET', 'POST'], 'widgets/add', 'AdminController::addWidget');
    $routes->match(['GET', 'POST'], 'widgets/edit/(:num)', 'AdminController::editWidget/$1');

    // Polls
    $routes->get('polls', 'AdminController::polls');
    $routes->match(['GET', 'POST'], 'polls/add', 'AdminController::addPoll');
    $routes->match(['GET', 'POST'], 'polls/edit/(:num)', 'AdminController::editPoll/$1');

    // Media
    $routes->get('media', 'MediaController::media');

    // Gallery
    $routes->get('gallery/albums', 'GalleryController::albums');
    $routes->match(['GET', 'POST'], 'gallery/albums/add', 'GalleryController::addAlbum');
    $routes->match(['GET', 'POST'], 'gallery/albums/edit/(:num)', 'GalleryController::editAlbum/$1');
    $routes->get('gallery/categories', 'GalleryController::categories');
    $routes->match(['GET', 'POST'], 'gallery/categories/add', 'GalleryController::addCategory');
    $routes->match(['GET', 'POST'], 'gallery/categories/edit/(:num)', 'GalleryController::editCategory/$1');
    $routes->get('gallery/images', 'GalleryController::images');
    $routes->match(['GET', 'POST'], 'gallery/images/add', 'GalleryController::addImage');
    $routes->match(['GET', 'POST'], 'gallery/images/edit/(:num)', 'GalleryController::editImage/$1');

    // Contact
    $routes->match(['GET', 'POST'], 'contact-messages', 'AdminController::contactMessages');

    // Comments
    $routes->match(['GET', 'POST'], 'comments', 'AdminController::comments');

    // Newsletter
    $routes->match(['GET', 'POST'], 'newsletter', 'AdminController::newsletter');

    // Reward System
    $routes->match(['GET', 'POST'], 'reward-system', 'RewardController::rewardSystem');
    $routes->get('reward-system/payouts', 'RewardController::payouts');
    $routes->match(['GET', 'POST'], 'reward-system/payouts/add-payout', 'RewardController::addPayout');
    $routes->get('reward-system/earnings', 'RewardController::earnings');
    $routes->get('reward-system/pageviews', 'RewardController::pageviews');

    // Author Earnings
    $routes->match(['GET', 'POST'], 'author-earnings', 'EarningsController::authorEarnings');
    $routes->get('set-payout-account', 'EarningsController::setPayoutAccount');

    // Ad Spaces
    $routes->get('ad-spaces', 'AdminController::adSpaces');

    // Premium Membership
    $routes->match(['GET', 'POST'], 'premium-membership/plans', 'PremiumController::plans');
    $routes->match(['GET', 'POST'], 'premium-membership/transactions', 'PremiumController::transactions');
    $routes->match(['GET', 'POST'], 'premium-membership/transaction-details/(:num)', 'PremiumController::transactionDetails/$1');
    $routes->match(['GET', 'POST'], 'premium-membership/plans/add', 'PremiumController::addPlan');
    $routes->match(['GET', 'POST'], 'premium-membership/plans/edit/(:num)', 'PremiumController::editPlan/$1');
    $routes->match(['GET', 'POST'], 'premium-membership/settings', 'PremiumController::settings');

    // Users
    $routes->get('users', 'UserController::users');
    $routes->get('users/premium', 'UserController::users/premium');
    $routes->get('users/details/(:num)', 'UserController::user/$1');
    $routes->match(['GET', 'POST'], 'users/add', 'UserController::addUser');
    $routes->match(['GET', 'POST'], 'users/edit/(:num)', 'UserController::editUser/$1');
    $routes->match(['GET', 'POST'], 'roles', 'UserController::roles');
    $routes->match(['GET', 'POST'], 'user/badges', 'UserController::badges');

    // Google News
    $routes->match(['GET', 'POST'], 'google-news', 'AdminController::googleNews');

    // SEO Tools
    $routes->match(['GET', 'POST'], 'seo-tools', 'AdminController::seoTools');

    // Storage
    $routes->match(['GET', 'POST'], 'storage', 'AdminController::storage');

    // Cache System
    $routes->match(['GET', 'POST'], 'cache-system', 'AdminController::cacheSystem');

    // Settings
    $routes->match(['GET', 'POST'], 'security/settings', 'AdminController::security');
    $routes->match(['GET', 'POST'], 'security/email-blacklist', 'AdminController::emailBlacklist');
    $routes->match(['GET', 'POST'], 'content-settings', 'AdminController::contentSettings');
    $routes->match(['GET', 'POST'], 'payment-settings', 'AdminController::paymentSettings');
    $routes->match(['GET', 'POST'], 'email-settings', 'AdminController::emailSettings');
    $routes->match(['GET', 'POST'], 'fonts', 'AdminController::fonts');
    $routes->get('social-login-settings', 'AdminController::socialLoginSettings');
    $routes->match(['GET', 'POST'], 'global-settings', 'AdminController::globalSettings');
    $routes->match(['GET', 'POST'], 'localized-settings', 'AdminController::localizedSettings');

    // Language
    $routes->match(['GET', 'POST'], 'language-settings', 'LanguageController::languageSettings');
    $routes->get('language/translations/(:num)', 'LanguageController::translations/$1');

    // Tags
    $routes->match(['GET', 'POST'], 'tags', 'CategoryController::tags');

    // Category Selector
    $routes->get('category-selector/list', 'CategoryController::selectorListCategories');
    $routes->get('category-selector/search', 'CategoryController::selectorSearchCategories');
});

/*
 * --------------------------------------------------------------------
 * AJAX Routes
 * --------------------------------------------------------------------
 */

$routes->group('', ['filter' => 'ajax'], function ($routes) {
    // Ajax
    $routes->post('Ajax/loadCaptcha', 'AjaxController::loadCaptcha');
    $routes->post('Ajax/checkTriviaQuizAnswer', 'AjaxController::checkTriviaQuizAnswer');
    $routes->post('Ajax/getQuizResult', 'AjaxController::getQuizResult');
    $routes->post('Ajax/addPostPollVote', 'AjaxController::addPostPollVote');
    $routes->post('Ajax/loadPostsByCategory', 'AjaxController::loadPostsByCategory');
    $routes->post('Ajax/toggleUserEmojiReaction', 'AjaxController::toggleUserEmojiReaction');
    $routes->post('Ajax/loadEmailTemplatePreview', 'AjaxController::loadEmailTemplatePreview');
    $routes->post('Ajax/toggleReadingListItem', 'AjaxController::toggleReadingListItem');
    $routes->post('Ajax/incrementPostViews', 'AjaxController::incrementPostViews');
    $routes->post('Ajax/addPollVote', 'AjaxController::addPollVote');

    // Admin
    $routes->post('Admin/populateUsersDropdown', 'AdminController::populateUsersDropdown');
    $routes->post('Admin/deleteFont', 'AdminController::deleteFont');

    // Reward
    $routes->post('Reward/approvePayout', 'RewardController::approvePayout');
    $routes->post('Reward/deletePayout', 'RewardController::deletePayout');

    // User
    $routes->post('User/toggleRewardSystem', 'UserController::toggleRewardSystem');
    $routes->post('User/verifyUserEmail', 'UserController::verifyUserEmail');
    $routes->post('User/toggleUserBan', 'UserController::toggleUserBan');
    $routes->post('User/deleteUser', 'UserController::deleteUser');
    $routes->post('User/deleteRole', 'UserController::deleteRole');

    // Language
    $routes->post('Language/deleteLanguage', 'LanguageController::deleteLanguage');
    $routes->post('Language/editTranslation', 'LanguageController::editTranslation');
    $routes->post('Category/populateCategoriesDropdown', 'CategoryController::populateCategoriesDropdown');

    // Post
    $routes->post('Post/fetchVideoFromURL', 'PostController::fetchVideoFromURL');
    $routes->post('Post/addListItem', 'PostController::addListItem');
    $routes->post('Post/addQuizItem', 'PostController::addQuizItem');
    $routes->post('Post/generateAiPost', 'PostController::generateAiPost');

    // Gallery
    $routes->post('Gallery/deleteImage', 'GalleryController::deleteImage');
    $routes->post('Gallery/uploadImage', 'GalleryController::uploadImage');
    $routes->post('Gallery/getAlbumsByLang', 'GalleryController::getAlbumsByLang');
    $routes->post('Gallery/getCategoriesByAlbum', 'GalleryController::getCategoriesByAlbum');

    // RSS
    $routes->post('Rss/deleteFeed', 'RssController::deleteFeed');
});

// Comments
$routes->group('comments', ['filter' => 'ajax'], function ($routes) {
    $routes->post('load', 'CommentController::loadComments');
    $routes->post('add', 'CommentController::addComment');
    $routes->post('delete', 'CommentController::deleteComment');
    $routes->post('like', 'CommentController::likeComment');
});

// File Manager
$routes->group($customRoutes->admin . '/file-manager', function ($routes) {
    $routes->get('list', 'MediaController::list');
    $routes->post('upload', 'MediaController::upload');
    $routes->post('update/(:num)', 'MediaController::update/$1');
    $routes->post('delete', 'MediaController::delete');
});

$routes->post('Post/initPostImport', 'PostController::initPostImport');
$routes->post('Post/processPostImportBatch', 'PostController::processPostImportBatch');

/*
 * --------------------------------------------------------------------
 * Static POST Routes
 * --------------------------------------------------------------------
 */

$postRoutesArray = [
    // Admin
    'Admin/deleteBlacklistEmail',
    'Admin/addFont',
    'Admin/adSpacesPost',
    'Admin/saveAdBannerPost',
    'Admin/deleteAdBannerPost',
    'Admin/toggleAdBannerStatusPost',
    'Admin/adsenseCodePost',
    'Admin/googleNews',
    'Admin/sitemapPost',
    'Admin/socialLoginSettingsPost',
    'Admin/setActiveLanguagePost',
    'Admin/downloadDatabaseBackup',
    'Admin/navigationSettingsPost',
    'Admin/deleteNewsletterEmail',
    'Admin/sendNewsletterBatch',
    'Admin/deletePage',
    'Admin/deletePoll',
    'Admin/deleteWidget',
    'Admin/getMenuLinksByLang',
    'Admin/deleteContactMessage',

    // Ajax
    'Ajax/sortMenuItems',
    'Ajax/loadMorePosts',
    'Ajax/loadMoreUsers',
    'Ajax/loadMoreNewsletterEmails',
    'Ajax/getQuizResults',
    'Ajax/deleteDropzoneUploadedFile',
    'Ajax/getTagSuggestions',
    'Ajax/addNewsletterEmail',

    // Category
    'Category/deleteCategory',
    'Category/deleteTag',

    // Checkout
    'Checkout/checkoutPost',

    // Gallery
    'Gallery/setImageAsAlbumCover',
    'Gallery/deleteAlbum',
    'Gallery/deleteCategory',

    // Language
    'Language/setDefaultLanguage',
    'Language/export',

    // User
    'User/changeUserRole',
    'User/assignSubscription',
    'User/editUserPost',

    // Post
    'Post/postAction',
    'Post/updatePostOrder',
    'Post/downloadCsvFile',
    'Post/deletePost',
    'Post/editPostPost',
    'Post/deleteSelectedPosts',
    'Post/postBulkOptionsPost',
    'Post/deletePostFile',

    // Premium
    'Premium/cancelActiveUserSubscription',

    //Rss
    'Rss/importFeedPosts',
];

foreach ($postRoutesArray as $item) {
    $array = explode('/', $item);
    $routes->post($item, $array[0] . 'Controller::' . $array[1]);
}

/*
 * --------------------------------------------------------------------
 * Dynamic Routes
 * --------------------------------------------------------------------
 */

if (!empty($languages)) {
    foreach ($languages as $language) {
        $key = '';
        if ($config->site_lang != $language->id) {
            $key = $language->short_form . '/';
            $routes->get($language->short_form, 'HomeController::index');
        }

        $routes->match(['GET', 'HEAD'], $key . $customRoutes->sign_up, 'AuthController::signUp');
        $routes->match(['GET', 'HEAD'], $key . $customRoutes->forgot_password, 'AuthController::forgotPassword');
        $routes->match(['GET', 'HEAD'], $key . $customRoutes->posts, 'HomeController::posts');
        $routes->match(['GET', 'HEAD'], $key . $customRoutes->tag . '/(:any)', 'HomeController::tag/$1');
        $routes->match(['GET', 'HEAD'], $key . $customRoutes->gallery . '/(:num)', 'HomeController::galleryAlbum/$1');
        $routes->match(['GET', 'HEAD'], $key . $customRoutes->search, 'HomeController::search');
        $routes->match(['GET', 'HEAD'], $key . $customRoutes->profile . '/(:any)', 'AccountController::profile/$1');
        $routes->match(['GET', 'POST'], $key . $customRoutes->account_settings, 'AccountController::accountSettings', ['filter' => 'auth']);
        $routes->match(['GET', 'POST'], $key . $customRoutes->account_settings . '/' . $customRoutes->social_accounts, 'AccountController::socialAccounts', ['filter' => 'auth']);
        $routes->match(['GET', 'POST'], $key . $customRoutes->account_settings . '/' . $customRoutes->manage_subscription, 'AccountController::manageSubscription', ['filter' => 'auth']);
        $routes->match(['GET', 'POST'], $key . $customRoutes->account_settings . '/' . $customRoutes->purchased_content, 'AccountController::purchasedContent', ['filter' => 'auth']);
        $routes->match(['GET', 'POST'], $key . $customRoutes->account_settings . '/' . $customRoutes->billing, 'AccountController::billing', ['filter' => 'auth']);
        $routes->match(['GET', 'POST'], $key . $customRoutes->account_settings . '/' . $customRoutes->payment_history, 'AccountController::paymentHistory', ['filter' => 'auth']);
        $routes->match(['GET', 'POST'], $key . $customRoutes->account_settings . '/' . $customRoutes->change_password, 'AccountController::changePassword', ['filter' => 'auth']);
        $routes->match(['GET', 'POST'], $key . $customRoutes->account_settings . '/' . $customRoutes->delete_account, 'AccountController::deleteAccount', ['filter' => 'auth']);

        $routes->get($key . $customRoutes->reading_list, 'HomeController::readingList', ['filter' => 'auth']);
        $routes->match(['GET', 'HEAD'], $key . $customRoutes->rss_feeds, 'FeedController::rssFeeds');
        $routes->match(['GET', 'HEAD'], $key . $customRoutes->rss_feeds . '/feed/latest', 'FeedController::rssFeeds/latest');
        $routes->match(['GET', 'HEAD'], $key . $customRoutes->rss_feeds . '/feed/category/(:any)', 'FeedController::rssFeeds/category/$1');
        $routes->match(['GET', 'HEAD'], $key . $customRoutes->rss_feeds . '/feed/author/(:any)', 'FeedController::rssFeeds/author/$1');
        $routes->get($key . 'post/preview/(:any)', 'HomeController::preview/$1');
        $routes->get($key . 'auth/reset-password', 'AuthController::resetPassword');
        $routes->get($key . 'auth/verify-email', 'AuthController::verifyEmail');

        $routes->match(['GET', 'POST'], $key . $customRoutes->subscription . '/' . $customRoutes->plans, 'CheckoutController::pricing');
        $routes->get($key . $customRoutes->checkout, 'CheckoutController::checkout');
        $routes->get($key . $customRoutes->checkout . '/' . $customRoutes->payment, 'CheckoutController::payment');
        $routes->get($key . $customRoutes->checkout . '/' . $customRoutes->success, 'CheckoutController::success');
        $routes->get($key . $customRoutes->payment . '/' . $customRoutes->invoice . '/(:num)', 'PaymentController::invoice/$1');

        if ($config->site_lang != $language->id) {
            $routes->match(['GET', 'HEAD'], $key . '(:any)/(:any)/(:any)', 'HomeController::error404');
            $routes->match(['GET', 'HEAD'], $key . '(:any)/(:any)', 'HomeController::subCategory/$1/$2');
            $routes->match(['GET', 'HEAD'], $key . '(:any)', 'HomeController::any/$1');
        }
    }
}

$routes->match(['GET', 'HEAD'], '(:any)/(:any)/(:any)', 'HomeController::error404');
$routes->match(['GET', 'HEAD'], '(:any)/(:any)', 'HomeController::subCategory/$1/$2');
$routes->match(['GET', 'HEAD'], '(:any)', 'HomeController::any/$1');