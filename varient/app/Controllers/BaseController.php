<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [
        'text', 'security'
    ];

    public $session;
    public $config;
    public $settings;
    public $activeLanguages;
    public $activeLang;
    public $activeFonts;
    public $isRtl;
    public $widgets;
    public $menuLinks;
    public $categoryTree;
    public $activeTheme;
    public $themeMode;
    public $viewsPath;
    public $assetsPath;
    public $latestCategoryPosts;
    public $showcasePosts;
    public $adSpaces;
    public $premiumMembership;
    public $postsPerPage;
    public $tablePerPage;

    /**
     * Constructor
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Prevent the application from being embedded in frames or iframes (clickjacking protection)
        $this->response->setHeader('Content-Security-Policy', "frame-ancestors 'none';");

        $this->session = \Config\Services::session();
        $this->request = \Config\Services::request();

        $appContext = service('appContext');

        // Config object
        $this->config = $appContext->config;

        // Set active language for POST request
        if (isPostMethod() && !empty(inputPost('sysLangId'))) {
            $appContext->setActiveLanguageById((int)inputPost('sysLangId'));
        }

        // Settings
        $this->settings = $appContext->settings;

        // Active languages
        $this->activeLanguages = $appContext->activeLanguages;

        // Active language
        $this->activeLang = $appContext->activeLang;

        // Active theme
        $this->activeTheme = $appContext->activeTheme;

        // Theme mode
        $this->themeMode = $this->activeTheme->theme_mode === 'dark' ? 'dark' : 'light';

        // RTL State
        $this->isRtl = false;
        if ($this->activeLang->text_direction === 'rtl') {
            $this->isRtl = true;
        }

        // Maintenance mode
        $this->maintenanceMode();

        // Site fonts
        $this->activeFonts = service('dataService')->getFonts($this->settings);

        // Widgets
        $this->widgets = service('dataService')->getWidgets((int)$this->activeLang->id);

        // Ad spaces
        $this->adSpaces = service('dataService')->getAdSpaces((int)$this->activeLang->id);

        // Menu links
        $this->menuLinks = service('dataService')->getMenuLinks((int)$this->activeLang->id, true);

        // Category tree (Depth = 1)
        $this->categoryTree = service('dataService')->getCategoryTree((int)$this->activeLang->id);

        // Latest category posts
        $this->latestCategoryPosts = service('postService')->getLatestPostsByCategories((int)$this->activeLang->id, $this->categoryTree);

        // Showcase Posts
        $this->showcasePosts = service('postService')->getShowcasePosts((int)$this->activeLang->id);

        // Set paths
        $themePath = getThemePath();
        $this->viewsPath = $themePath;
        $this->assetsPath = 'assets/' . $themePath;

        // Premium membership
        $this->premiumMembership = $appContext->premiumMembership;

        // Posts per page (Pagination)
        $this->postsPerPage = (int)($this->config->pagination_per_page ?? 16);

        // Table records per page
        $this->tablePerPage = 10;

        // Update last seen
        $this->updateLastSeen($this->session);

        // View variables
        $view = \Config\Services::renderer();
        $view->setData([
            'activeTheme'         => $this->activeTheme,
            'themeMode'           => $this->themeMode,
            'viewsPath'           => $this->viewsPath,
            'assetsPath'          => $this->assetsPath,
            'activeLang'          => $this->activeLang,
            'config'              => $this->config,
            'baseSettings'        => $this->settings,
            'activeLanguages'     => $this->activeLanguages,
            'isRtl'               => $this->isRtl,
            'activeFonts'         => $this->activeFonts,
            'menuLinks'           => $this->menuLinks,
            'widgets'             => $this->widgets,
            'categoryTree'        => $this->categoryTree,
            'latestCategoryPosts' => $this->latestCategoryPosts,
            'showcasePosts'       => $this->showcasePosts,
            'adSpaces'            => $this->adSpaces,
            'premiumMembership'   => $this->premiumMembership,
            'postsPerPage'        => $this->postsPerPage
        ]);
    }

    /**
     * Maintenance Mode
     */
    private function maintenanceMode()
    {
        $maintenanceMode = $this->config->maintenance_mode_settings ?? null;

        if (empty($maintenanceMode) || empty($maintenanceMode->status) || isSuperAdmin()) {
            return;
        }

        $router = service('router');
        $controller = (string)$router->controllerName();
        $method = (string)$router->methodName();

        if (strpos($controller, 'AuthController') !== false && $method === 'login') {
            return;
        }

        return view('common/maintenance', [
            'maintenanceMode' => $maintenanceMode,
            'baseSettings'    => $this->settings
        ]);
    }

    /**
     * Updates the authenticated user's 'last_seen' timestamp
     */
    protected function updateLastSeen($session)
    {
        if (!authCheck()) {
            return;
        }

        // Throttling Mechanism
        $lastChecked = $session->get('last_seen_checked');
        $throttleTime = 180;  // 3 minutes = 180 seconds

        if ($lastChecked && (time() - $lastChecked < $throttleTime)) {
            return;
        }

        $userId = user()->id ?? null;
        if ($userId) {
            model('UserModel')->updateLastSeen((int)$userId);

            $session->set('last_seen_checked', time());
        }
    }

    /**
     * Error 404
     */
    public function error404()
    {
        $data = [
            'title'       => '404 - ' . trans('page_not_found'),
            'description' => trans('page_not_found_sub'),
            'keywords'    => '404, error, not found',
            'homeTitle'   => $this->settings->home_title,
        ];

        return service('response')
            ->setStatusCode(404)
            ->setBody(view('errors/html/error_404', $data));
    }
}