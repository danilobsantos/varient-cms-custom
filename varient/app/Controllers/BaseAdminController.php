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
abstract class BaseAdminController extends Controller
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
        'text', 'security', 'ui'
    ];

    public $session;
    public $config;
    public $settings;
    public $activeLanguages;
    public $activeTheme;
    public $activeLang;
    public $isRtl;
    public $aiWriter;
    public $perPage;
    public $premiumMembership;

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

        // Load App Context Service
        $appContext = service('appContext');

        // Config object
        $this->config = $appContext->config;

        // Settings
        $this->settings = $appContext->settings;

        // Set control panel language
        if (!empty($this->session->get('vr_control_panel_lang'))) {
            $appContext->setActiveLanguageById((int)$this->session->get('vr_control_panel_lang'));
        }

        // Active languages
        $this->activeLanguages = $appContext->activeLanguages;

        // Active language
        $this->activeLang = $appContext->activeLang;

        // Check auth
        if (!authCheck()) {
            redirect()->to(adminUrl('login'))->send();
            exit;
        }

        // Check permission
        if (!hasAdminPanelAccess()) {
            redirect()->to(langBaseUrl())->send();
            exit;
        }

        // Maintenance mode
        $maintenanceMode = $this->config->maintenance_mode_settings ?? null;
        if (!empty($maintenanceMode) && !empty($maintenanceMode->status) && !isSuperAdmin()) {
            $authService = service('authService');
            $authService->logout();
            redirect()->to(adminUrl('login'))->send();
            exit;
        }

        // Active theme
        $this->activeTheme = getContextValue('activeTheme');

        // RTL State
        $this->isRtl = false;
        if ($this->activeLang->text_direction === 'rtl') {
            $this->isRtl = true;
        }

        // Posts per page / Pagination
        $show = (int)inputGet('show');
        $this->perPage = $show > 0 ? $show : 20;

        // Premium membership
        $this->premiumMembership = $appContext->premiumMembership;

        // AI Writer
        $aiStatus = (int)($this->config->ai_writer->status ?? 0);
        if (!hasPermission('ai_writer')) {
            $aiStatus = 0;
        }
        $this->aiWriter = (object)[
            'status'  => $aiStatus,
            'models'  => \App\Libraries\AIWriter::$models,
            'tones'   => \App\Libraries\AIWriter::$tones,
            'lengths' => \App\Libraries\AIWriter::$lengths
        ];

        // Run background tasks
        if (strtolower($this->request->getMethod()) === 'get' && !$this->request->isAJAX()) {
            $this->handleScheduledTasks();
        }

        // View variables
        $view = \Config\Services::renderer();
        $view->setData([
            'activeLang'        => $this->activeLang,
            'activeLanguages'   => $this->activeLanguages,
            'activeTheme'       => $this->activeTheme,
            'isRtl'             => $this->isRtl,
            'config'            => $this->config,
            'baseSettings'      => $this->settings,
            'premiumMembership' => $this->premiumMembership,
            'aiWriter'          => $this->aiWriter
        ]);
    }

    /**
     * Handles scheduled background tasks (Pseudo-Cron) every 1 hour
     */
    protected function handleScheduledTasks(): void
    {
        $securitySettings = getSecuritySettings();

        // Get the last run time from config
        $lastRunTime = $this->config->last_system_cron_run ?? '2026-01-01 00:00:00';

        // Check if at least 1 hour (3600 seconds) has passed
        if (time() - strtotime($lastRunTime) >= 3600) {

            // Race Condition Prevention: Update the timestamp BEFORE running tasks
            $newTime = dateTimeNow();
            model('ConfigModel')->update(1, ['last_system_cron_run' => $newTime]);

            // Start Scheduled Operations
            try {
                // Cleanup login attempts
                $lockoutTime = (int)($securitySettings->lockout_time ?? 300);
                model('LoginAttemptModel')->cleanupOldAttempts($lockoutTime);

                // Cleanup old pageview records
                model('PageViewsModel')->deleteOldRecords();

                // Cleanup expired sessions
                model('SessionModel')->purgeExpired();

                // Delete incomplete orders
                model('OrderModel')->deleteOldIncompleteOrders();

            } catch (\Exception $e) {
            }
        }
    }
}
