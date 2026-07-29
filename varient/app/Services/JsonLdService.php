<?php

namespace App\Services;

/**
 * A service class responsible for generating structured data (JSON-LD)
 * for search engine optimization.
 */
class JsonLdService
{
    /**
     * The main method to generate the JSON-LD script tag.
     *
     * @param array $types An array of schema types to generate.
     * @param array $data  An associative array containing all necessary data objects.
     * @return string The complete <script> tag with the JSON-LD data, or an empty string.
     */
    public function generate(array $types, array $data = []): string
    {
        $schemas = [];

        foreach ($types as $type) {
            $schema = null;
            switch ($type) {
                case 'website':
                    $schema = $this->buildWebsiteSchema();
                    break;
                case 'organization':
                    $schema = $this->buildOrganizationSchema($data);
                    break;
                case 'news_article':
                    $schema = $this->buildNewsArticleSchema($data);
                    break;
                case 'recipe':
                    $schema = $this->buildRecipeSchema($data);
                    break;
                case 'event':
                    $schema = $this->buildEventSchema($data);
                    break;
                case 'breadcrumb':
                    $schema = $this->buildBreadcrumbSchema($data);
                    break;
            }

            if (!empty($schema)) {
                $schemas[] = $schema;
            }
        }

        if (empty($schemas)) {
            return '';
        }

        $outputSchema = count($schemas) > 1
            ? ['@context' => 'https://schema.org', '@graph' => $schemas]
            : array_merge(['@context' => 'https://schema.org'], $schemas[0]);

        $json = json_encode($outputSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return '<script type="application/ld+json">' . "\n" . $json . "\n" . '</script>';
    }

    /**
     * Builds the JSON-LD schema for the main website.
     *
     * @return array
     */
    private function buildWebsiteSchema(): array
    {
        return [
            '@type'           => 'WebSite',
            'url'             => base_url(),
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => base_url() . '/search?q={search_term_string}',
                'query-input' => 'required name=search_term_string'
            ]
        ];
    }

    /**
     * Builds the JSON-LD schema for the organization.
     *
     * @param array $data
     * @return array
     */
    private function buildOrganizationSchema(array $data): array
    {
        $baseSettings = $data['baseSettings'] ?? null;
        $socialObjects = $baseSettings ? getSiteSocialLinks($baseSettings) : [];
        $socialLinks = [];

        if (!empty($socialObjects)) {
            foreach ($socialObjects as $item) {
                if (!empty($item->url)) {
                    $socialLinks[] = escMeta($item->url);
                }
            }
        }

        $schema = [
            '@type' => 'Organization',
            'name'  => $baseSettings->application_name ?? 'Website Name',
            'url'   => base_url(),
            'logo'  => [
                '@type'  => 'ImageObject',
                'width'  => 600,
                'height' => 60,
                'url'    => getLogo()
            ]
        ];

        if (!empty($socialLinks)) {
            $schema['sameAs'] = $socialLinks;
        }

        return $schema;
    }

    /**
     * Builds the JSON-LD schema for breadcrumbs.
     *
     * @param array $data
     * @return array
     */
    private function buildBreadcrumbSchema(array $data): array
    {
        $breadcrumbTree = $data['breadcrumb'] ?? [];

        if (empty($breadcrumbTree)) {
            return [];
        }

        $elements = [];
        $position = 1;

        foreach ($breadcrumbTree as $item) {
            $itemUrl = !empty($item->url) ? $item->url : generateCategoryUrl($item);

            if (empty($itemUrl)) {
                continue;
            }

            $elements[] = [
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => escMeta($item->name ?? $item->cat_name ?? ''),
                'item'     => $itemUrl
            ];
        }

        if (empty($elements)) {
            return [];
        }

        return [
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $elements
        ];
    }

    /**
     * Builds the JSON-LD schema for a News Article.
     *
     * @param array $data
     * @return array
     */
    private function buildNewsArticleSchema(array $data): array
    {
        $post = $data['post'] ?? null;
        $postVideo = $data['postVideo'] ?? null;
        $baseSettings = $data['baseSettings'] ?? null;
        $activeLang = $data['activeLang'] ?? null;

        if (empty($post)) {
            return [];
        }

        $dateModified = !empty($post->updated_at) ? $post->updated_at : $post->created_at;
        $keywords = !empty($post->meta_keywords) ? explode(',', escMeta($post->meta_keywords)) : null;

        $premiumSettings = getContextValue('config')->premium_membership_settings ?? null;
        $isPremiumActive = (int)($premiumSettings->subscription_status ?? 0);
        $isExclusiveActive = (int)($premiumSettings->exclusive_sale_status ?? 0);
        $isPremium = (!empty($post->is_premium) && $isPremiumActive) || (!empty($post->is_exclusive) && $isExclusiveActive);

        $schema = [
            '@type'               => 'NewsArticle',
            'mainEntityOfPage'    => [
                '@type' => 'WebPage',
                '@id'   => generatePostUrl($post)
            ],
            'headline'            => escMeta($post->title),
            'name'                => escMeta($post->title),
            'datePublished'       => date('c', strtotime($post->created_at)),
            'dateModified'        => date('c', strtotime($dateModified)),
            'inLanguage'          => $activeLang->language_code ?? 'en',
            'isAccessibleForFree' => !$isPremium,
            'author'              => [
                '@type' => 'Person',
                'name'  => escMeta($post->author_username ?? ''),
                'url'   => base_url("profile/" . ($post->author_slug ?? ''))
            ],
            'publisher'           => [
                '@type' => 'Organization',
                'name'  => $baseSettings->application_name ?? 'Website Name',
                'logo'  => [
                    '@type'  => 'ImageObject',
                    'width'  => 600,
                    'height' => 60,
                    'url'    => getLogo()
                ]
            ],
            'image'               => [
                '@type'      => 'ImageObject',
                'url'        => getPostImageUrl($post, 'big'),
                'contentUrl' => getPostImageUrl($post, 'big'),
                'width'      => 870,
                'height'     => 580
            ]
        ];

        if (!$isPremium) {
            $articleBody = strip_tags($post->content ?? $post->summary ?? '');
            $articleBodyLimiter = function_exists('characterLimiter')
                ? characterLimiter($articleBody, 5000)
                : mb_substr($articleBody, 0, 5000);
            
            $schema['articleBody'] = escMeta($articleBodyLimiter);
        }

        if (!empty($postVideo) || !empty($post->video_url) || !empty($post->video_embed_code)) {
            $videoUrl = '';
            if (!empty($postVideo->file_path)) {
                $videoUrl = getStorageFileUrl($postVideo->file_path, $postVideo->storage ?? '');
            } elseif (!empty($post->video_url)) {
                $videoUrl = $post->video_url;
            }

            $schema['video'] = [
                '@type'        => 'VideoObject',
                'name'         => escMeta($post->title),
                'description'  => escMeta(characterLimiter(strip_tags($post->summary ?? ''), 200, '...')),
                'thumbnailUrl' => [getPostImageUrl($post, 'big')],
                'uploadDate'   => date('c', strtotime($post->created_at)),
                'embedUrl'     => !empty($post->video_embed_url) ? escMeta($post->video_embed_url) : $videoUrl
            ];

            if (!empty($videoUrl)) {
                $schema['video']['contentUrl'] = $videoUrl;
            }
        }

        if (!empty($keywords)) {
            $schema['keywords'] = $keywords;
        }

        return $schema;
    }

    /**
     * Builds the JSON-LD schema for a Recipe.
     *
     * @param array $data
     * @return array
     */
    private function buildRecipeSchema(array $data): array
    {
        $post = $data['post'] ?? null;
        $dataRecipe = $data['dataRecipe'] ?? null;
        $postListItems = $data['postListItems'] ?? [];

        if (empty($post)) {
            return [];
        }

        $prepTimeVal = !empty($dataRecipe->prep_time) ? (int)$dataRecipe->prep_time : 0;
        $cookTimeVal = !empty($dataRecipe->cook_time) ? (int)$dataRecipe->cook_time : 0;
        $totalTimeVal = $prepTimeVal + $cookTimeVal;

        $premiumSettings = getContextValue('config')->premium_membership_settings ?? null;
        $isPremiumActive = (int)($premiumSettings->subscription_status ?? 0);
        $isExclusiveActive = (int)($premiumSettings->exclusive_sale_status ?? 0);
        $isPremium = (!empty($post->is_premium) && $isPremiumActive) || (!empty($post->is_exclusive) && $isExclusiveActive);

        $schema = [
            '@type'               => 'Recipe',
            'name'                => escMeta($post->title),
            'image'               => [
                '@type'  => 'ImageObject',
                'url'    => getPostImageUrl($post, 'big'),
                'width'  => 750,
                'height' => 500
            ],
            'author'              => [
                '@type' => 'Person',
                'name'  => escMeta($post->author_username ?? ''),
                'url'   => base_url("profile/" . ($post->author_slug ?? ''))
            ],
            'datePublished'       => !empty($post->created_at) ? date('c', strtotime($post->created_at)) : null,
            'description'         => escMeta($post->summary ?? ''),
            'prepTime'            => $prepTimeVal > 0 ? "PT{$prepTimeVal}M" : null,
            'cookTime'            => $cookTimeVal > 0 ? "PT{$cookTimeVal}M" : null,
            'totalTime'           => $totalTimeVal > 0 ? "PT{$totalTimeVal}M" : null,
            'recipeYield'         => !empty($dataRecipe->serving) ? "{$dataRecipe->serving} servings" : "1 serving",
            'isAccessibleForFree' => !$isPremium
        ];

        if (!$isPremium) {
            if (!empty($postListItems)) {
                $instructions = [];
                foreach ($postListItems as $item) {
                    $instructions[] = escMeta($item->title ?? $item->content ?? '');
                }
                if (!empty($instructions)) {
                    $schema['recipeInstructions'] = $instructions;
                }
            }
        }

        return array_filter($schema, function ($value) {
            return !empty($value) || $value === 0;
        });
    }

    /**
     * Builds the JSON-LD schema for an Event.
     *
     * @param array $data
     * @return array
     */
    private function buildEventSchema(array $data): array
    {
        $post = $data['post'] ?? null;

        if (empty($post)) {
            return [];
        }

        $eventDetails = $post->post_data->details ?? null;

        $eventDate = $eventDetails->date ?? '';
        $startTime = $eventDetails->start_time ?? '';
        $endTime = $eventDetails->end_time ?? '';
        $venueName = $eventDetails->venue_name ?? $eventDetails->location ?? 'Event Venue';
        $address = $eventDetails->map_address ?? '';
        $lat = $eventDetails->map_latitude ?? '';
        $lng = $eventDetails->map_longitude ?? '';
        $organizer = $eventDetails->organizer ?? '';

        $startDateStr = trim("$eventDate $startTime");
        $endDateStr = trim("$eventDate $endTime");

        $startDate = !empty($startDateStr) ? date('c', strtotime($startDateStr)) : date('c', strtotime($post->created_at));
        $endDate = (!empty($endDateStr) && !empty($endTime)) ? date('c', strtotime($endDateStr)) : null;

        $premiumSettings = getContextValue('config')->premium_membership_settings ?? null;
        $isPremiumActive = (int)($premiumSettings->subscription_status ?? 0);
        $isExclusiveActive = (int)($premiumSettings->exclusive_sale_status ?? 0);
        $isPremium = (!empty($post->is_premium) && $isPremiumActive) || (!empty($post->is_exclusive) && $isExclusiveActive);

        $schema = [
            '@type'               => 'Event',
            'name'                => escMeta($post->title),
            'description'         => escMeta(characterLimiter(strip_tags($post->summary ?? $post->content ?? ''), 500)),
            'startDate'           => $startDate,
            'image'               => [getPostImageUrl($post, 'big')],
            'eventStatus'         => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'isAccessibleForFree' => !$isPremium
        ];

        if (!empty($endDate)) {
            $schema['endDate'] = $endDate;
        }

        if (!empty($venueName) || !empty($address)) {
            $locationSchema = [
                '@type' => 'Place',
                'name'  => escMeta($venueName),
            ];

            if (!empty($address)) {
                $locationSchema['address'] = [
                    '@type'           => 'PostalAddress',
                    'streetAddress'   => escMeta($address)
                ];
            }

            if (!empty($lat) && !empty($lng)) {
                $locationSchema['geo'] = [
                    '@type'     => 'GeoCoordinates',
                    'latitude'  => escMeta($lat),
                    'longitude' => escMeta($lng)
                ];
            }

            $schema['location'] = $locationSchema;
        }

        if (!empty($organizer)) {
            $schema['organizer'] = [
                '@type' => 'Organization',
                'name'  => escMeta($organizer)
            ];
        }

        return array_filter($schema, function ($value) {
            return !empty($value) || $value === 0;
        });
    }
}