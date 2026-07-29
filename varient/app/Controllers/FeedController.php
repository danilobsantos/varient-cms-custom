<?php

namespace App\Controllers;

class FeedController extends BaseController
{
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    /**
     * Rss Feeds
     */
    public function rssFeeds(?string $type = null, ?string $slug = null)
    {
        if ((int)$this->config->show_rss !== 1) {
            return $this->error404();
        }

        $slug = $slug ? cleanSlug($slug) : null;
        $limit = (int)($this->config->rss_max_posts_per_feed ?? 50);
        $langId = (int)$this->activeLang->id;
        $data = [];
        $showPostContent = $this->config->rss_content_type == 'content' ? true : false;

        if ($type === 'latest') {

            $data = [
                'encoding'        => 'utf-8',
                'feedName'        => trans('latest_posts'),
                'feedURL'         => generateURL('rss_feeds') . '/feed/latest',
                'description'     => $this->settings->site_title . ' - ' . trans('latest_posts'),
                'posts'           => service('postService')->getRssPosts($langId, $type, 0, $limit),
                'showPostContent' => $showPostContent
            ];

        } elseif ($type === 'category' && $slug) {

            $category = model('CategoryModel')->findRootBySlug($slug, $langId);
            if ($category) {
                $data = [
                    'encoding'        => 'utf-8',
                    'feedName'        => $category->name,
                    'feedURL'         => generateURL('rss_feeds') . '/feed/category/' . $category->slug,
                    'description'     => $this->settings->site_title . ' - ' . $category->name,
                    'posts'           => service('postService')->getRssPosts($langId, $type, $category->id, $limit),
                    'showPostContent' => $showPostContent
                ];
            }

        } elseif ($type === 'author' && $slug) {

            $user = model('UserModel')->findBySlug($slug);
            if ($user) {
                $data = [
                    'encoding'        => 'utf-8',
                    'feedName'        => $user->username,
                    'feedURL'         => generateURL('rss_feeds') . '/feed/author/' . $user->slug,
                    'description'     => $this->settings->site_title . ' - ' . $user->username,
                    'posts'           => service('postService')->getRssPosts($langId, $type, $user->id, $limit),
                    'showPostContent' => $showPostContent
                ];
            }
        }

        if ($type && empty($data)) {
            return $this->error404();
        }

        if (!empty($data)) {
            return $this->response
                ->setContentType('text/xml', 'utf-8')
                ->setBody(view('themes/common/rss/feed', $data));
        }

        $pageData = setPageMeta(trans('rss_feeds'));
        return view('themes/common/rss/rss_feeds', $pageData);
    }

    /**
     * Google News Sitemap
     */
    public function googleNewsSitemap()
    {
        $status = (int)($this->config->google_news_settings?->status ?? 0);
        if ($status !== 1) {
            return $this->error404();
        }

        $data = [
            'siteName' => $this->config->google_news_settings?->site_name ?? 'My News',
            'posts'    => service('postService')->getGoogleNewsSitemapPosts()
        ];

        return $this->response
            ->setContentType('text/xml', 'utf-8')
            ->setBody(view('common/news_sitemap', $data));
    }

    /**
     * Google News RSS
     */
    public function googleNewsRss(?int $categoryId = null)
    {
        $settings = $this->config->google_news_settings;

        if ((int)($settings?->status ?? 0) !== 1) {
            return $this->error404();
        }

        // Language
        $langId = (int)inputGet('lang') ?: (int)$this->activeLang->id;

        // Category (optional)
        $category = null;
        if ($categoryId) {
            $category = model('CategoryModel')->where(['id' => $categoryId, 'status' => 1])->first();
        }

        $feedName = $category?->name ?? trans('all_posts');

        $data = [
            'feedName'        => $feedName,
            'posts'           => service('postService')->getGoogleNewsFeedPosts($langId, $categoryId, (int)($settings?->post_limit ?? 0)),
            'lang'            => $this->activeLang->short_form ?? 'en',
            'baseUrl'         => generateBaseURLByLangId($langId),
            'description'     => trim(($settings?->site_name ?? '') . ' - ' . $feedName),
            'showPostContent' => ($settings?->content_type ?? '') === 'content',
        ];

        return $this->response
            ->setContentType('application/xml', 'utf-8')
            ->setBody(view('themes/common/rss/feed', $data));
    }

    /**
     * Generate the dynamic PWA (Progressive Web App) manifest.json file
     */
    public function manifest($lang = null)
    {
        $queryLang = inputGet('lang');

        $langCode = !empty($queryLang) ? $queryLang : $lang;

        $language = model('LanguageModel')->findByShortForm($langCode);
        if (empty($language)) {
            $language = $this->activeLang;
        }

        $baseUrl = generateBaseURLByLang($language);
        $settings = model('SettingsModel')->findByLangId($language->id);

        $appName = !empty($settings->application_name)
            ? $settings->application_name
            : ($settings->site_title ?? 'Varient');

        $themeColor = $settings->theme_color ?? '#FFFFFF';
        $backgroundColor = $settings->background_color ?? '#FFFFFF';

        $manifest = [
            'dir'              => $language->text_direction == 'rtl' ? 'rtl' : 'ltr',
            'name'             => $appName,
            'description'      => $settings->site_description ?? '',
            'short_name'       => $settings->application_name ?? 'Varient',
            'icons'            => [
                ['src' => getAppIcon(144), 'sizes' => '144x144', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => getAppIcon(192), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => getAppIcon(512), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable']
            ],
            'scope'            => '/',
            'start_url'        => $baseUrl,
            'display'          => 'standalone',
            'orientation'      => 'any',
            'theme_color'      => $themeColor,
            'background_color' => $backgroundColor,
            'lang'             => $language->short_form,
            'screenshots'      => []
        ];

        return $this->response->setJSON($manifest, true, true);
    }
}
