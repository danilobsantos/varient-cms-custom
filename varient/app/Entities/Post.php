<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Post extends Entity
{
    /**
     * Defines the data types for the entity attributes
     */
    protected $casts = [
        'id'                => 'integer',
        'lang_id'           => 'integer',
        'category_id'       => 'integer',
        'image_id'          => 'integer',
        'video_id'          => 'integer',
        'user_id'           => 'integer',
        'feed_id'           => 'integer',
        'pageviews'         => 'integer',
        'comment_count'     => 'integer',
        'slider_order'      => 'integer',
        'featured_order'    => 'integer',
        'need_auth'         => 'boolean',
        'is_scheduled'      => 'boolean',
        'visibility'        => 'boolean',
        'full_width_post'   => 'boolean',
        'status'            => 'boolean',
        'show_post_url'     => 'boolean',
        'show_item_numbers' => 'boolean',
        'is_poll_public'    => 'boolean',
        'is_premium'        => 'boolean',
        'is_exclusive'      => 'boolean',
        'exclusive_price'   => 'float',
        'link_list_style'   => 'json',
        'post_data'         => 'json',
        'extra_data'        => 'json',
        'faq'               => 'json',
        'title'             => 'string',
        'slug'              => 'string',
        'guid'              => 'string',
        'summary'           => 'string',
        'content'           => 'string',
        'optional_url'      => 'string',
        'post_format'       => 'string',
        'image_url'         => 'string',
        'video_url'         => 'string',
        'video_embed_code'  => 'string',
        'post_url'          => 'string',
        'image_description' => 'string',
        'meta_title'        => 'string',
        'meta_keywords'     => 'string',
        'meta_description'  => 'string',
    ];

    /**
     * Attributes that should be mutated to Time objects
     */
    protected $dates = ['event_start_date', 'event_end_date', 'scheduled_at', 'updated_at', 'created_at'];

    /**
     * Main handler for incoming post form data
     */
    public function handlePostForm(array $formData, array $defaultPostFormats, int $defaultLangId, int $userId): void
    {
        $this->lang_id = !empty($formData['lang_id']) ? $formData['lang_id'] : $defaultLangId;
        $this->user_id = !empty($formData['user_id']) ? $formData['user_id'] : $userId;

        $this->processSlug($formData);
        $this->processPostFormat($formData, $defaultPostFormats);
        $this->handlePostPublish($formData);

        if ($this->post_format === 'table_of_contents') {
            $this->processPostListItemsStyles($formData);
        }

        $this->handleFaqData($formData);

        if ($this->post_format === 'recipe') {
            $this->handleRecipeData($formData);
        }

        $this->handleVideoEmbedCode($formData);

        $exclusivePrice = $formData['exclusive_price'] ?? null;
        if (!empty($exclusivePrice)) {
            $this->exclusive_price = numToDecimal($exclusivePrice);
        }
    }

    /**
     * Cleans special characters and formats the slug
     */
    private function processSlug(array $formData): void
    {
        $slug = '';

        if (!empty($formData['slug'])) {
            $slug = strSlug($formData['slug']);
        }

        if (empty($slug) && !empty($formData['title'])) {
            $slug = strSlug($formData['title']);
        }

        $this->slug = empty($slug) ? 'post-' . uniqid() : $slug;
    }

    /**
     * Validates and sets the post format
     */
    private function processPostFormat(array $formData, array $defaultPostFormats): void
    {
        $formPostFormat = $formData['post_format'] ?? '';
        $this->post_format = in_array($formPostFormat, $defaultPostFormats) ? $formPostFormat : 'article';
    }

    /**
     * Prepares list styles for table_of_contents post formats
     */
    private function processPostListItemsStyles(array $formData): void
    {
        $linListStyles = [];
        $allowedStyles = getAppDefault('cssListStyles');

        if (!empty($formData['link_list_style']) && is_array($formData['link_list_style'])) {
            $index = 1;
            foreach ($formData['link_list_style'] as $arr) {
                $style = !empty($arr['style']) && in_array($arr['style'], $allowedStyles) ? $arr['style'] : 'none';

                $linListStyles[$index] = [
                    'style'  => $style,
                    'status' => !empty($arr['status']) ? 1 : 0,
                ];
                $index++;
            }
        }

        $this->link_list_style = $linListStyles;
    }

    /**
     * Prepares recipe data for both insert and update operations
     */
    private function handleRecipeData(array $formData): void
    {
        $data = [
            'prep_time'   => $formData['prep_time'] ?? '',
            'cook_time'   => $formData['cook_time'] ?? '',
            'serving'     => $formData['serving'] ?? '',
            'difficulty'  => $formData['difficulty'] ?? '',
            'ingredients' => $formData['recipe_ingredient'] ?? []
        ];

        $nInfo = [];
        $items = $formData['recipe_nutritional'] ?? [];

        foreach ($items as $arr) {
            $nInfo[] = [
                'n' => $arr['name'] ?? '',
                'v' => $arr['value'] ?? '',
            ];
        }
        $data['nInfo'] = $nInfo;

        $this->post_data = $data;
    }

    /**
     * Prepares FAQ data for both insert and update operations
     */
    private function handleFaqData(array $formData): void
    {
        $cleanFaq = [];

        if (isset($formData['faq']) && is_array($formData['faq'])) {
            $cleanFaq = [
                'title' => $formData['faq_title'] ?? '',
                'items' => []
            ];

            foreach ($formData['faq'] as $item) {
                $question = trim($item['q'] ?? '');
                $answer = trim($item['a'] ?? '');

                if ($question !== '' && $answer !== '') {
                    $cleanFaq['items'][] = [
                        'q' => $question,
                        'a' => $answer
                    ];
                }
            }
        }

        $this->faq = $cleanFaq;
    }

    /**
     * Handles the processing of event details data, schedule, highlights, speakers, and registration
     */
    public function handleEventData(array $formData, array $avatars): void
    {
        $eventData = [];

        $eventData['details'] = [
            'venue_name'                   => $formData['event_venue_name'] ?? '',
            'location'                     => $formData['event_address'] ?? '',
            'organizer'                    => $formData['event_organizer'] ?? '',
            'highlights_title'             => $formData['event_highlights_title'] ?? '',
            'schedule_title'               => $formData['event_schedule_title'] ?? '',
            'speakers_title'               => $formData['event_speakers_title'] ?? '',
            'map_address'                  => $formData['event_map_address'] ?? '',
            'map_latitude'                 => $formData['event_map_latitude'] ?? '',
            'map_longitude'                => $formData['event_map_longitude'] ?? '',
            'registration_type'            => $formData['registration_type'] ?? 'none',
            'registration_button_text'     => $formData['registration_button_text'] ?? '',
            'registration_button_url'      => $formData['registration_button_url'] ?? '',
            'registration_capacity'        => $formData['registration_capacity'] ?? '',
            'registration_deadline'        => $formData['registration_deadline'] ?? '',
            'registration_access'          => $formData['registration_access'] ?? '',
            'registration_success_message' => $formData['registration_success_message'] ?? ''
        ];

        $eventData['highlights'] = [];
        $eventData['schedule'] = [];
        $eventData['speakers'] = [];
        $eventData['registration_fields'] = [];

        // Process Schedule
        if (isset($formData['event_schedule']) && is_array($formData['event_schedule'])) {
            foreach ($formData['event_schedule'] as $item) {
                $time = trim($item['time'] ?? '');
                $title = trim($item['title'] ?? '');
                $description = trim($item['description'] ?? '');

                if ($title !== '') {
                    $eventData['schedule'][] = [
                        'time'        => $time,
                        'title'       => $title,
                        'description' => $description
                    ];
                }
            }
        }

        // Process Highlights
        if (isset($formData['event_highlight']) && is_array($formData['event_highlight'])) {
            foreach ($formData['event_highlight'] as $item) {
                $title = trim($item['title'] ?? '');
                $value = trim($item['value'] ?? '');

                if ($title !== '') {
                    $eventData['highlights'][] = [
                        't' => $title,
                        'v' => $value
                    ];
                }
            }
        }

        // Process Custom Registration Fields (Repeater)
        if (isset($formData['reg_fields']) && is_array($formData['reg_fields'])) {
            foreach ($formData['reg_fields'] as $item) {
                $label = trim($item['label'] ?? '');
                $type = trim($item['type'] ?? 'text');
                $options = trim($item['options'] ?? '');

                if ($label !== '') {
                    $eventData['registration_fields'][] = [
                        'label'   => $label,
                        'type'    => $type,
                        'options' => $options
                    ];
                }
            }
        }

        // Process Speakers & Avatars
        $avatarsMap = [];
        if (!empty($avatars)) {
            foreach ($avatars as $av) {
                $avatarsMap[$av->id] = $av;
            }
        }

        if (isset($formData['speakers']) && is_array($formData['speakers'])) {
            foreach ($formData['speakers'] as $item) {
                $avatarId = trim($item['avatar'] ?? '');
                $foundAvatar = $avatarsMap[$avatarId] ?? null;

                $eventData['speakers'][] = [
                    'avatarId'    => $avatarId,
                    'avatarPath'  => $foundAvatar ? $foundAvatar->image_small : '',
                    'storage'     => $foundAvatar ? $foundAvatar->storage : 'local',
                    'name'        => trim($item['name'] ?? ''),
                    'description' => trim($item['description'] ?? '')
                ];
            }
        }

        // Assign to Entity Properties
        $this->event_start_date = $formData['event_start_date'] ?? '';
        $this->event_end_date = $formData['event_end_date'] ?? '';
        $this->post_data = $eventData;

        $this->extra_data = [
            'registration_deadline' => $formData['registration_deadline'] ?? ''
        ];

    }

    /**
     * Determines post status (draft, scheduled, published) and manages timestamps
     */
    private function handlePostPublish(array $formData): void
    {
        $isScheduled = $formData['is_scheduled'] ?? false;
        $isDraft = $formData['save_as_draft'] ?? false;
        $scheduledAt = $formData['scheduled_at'] ?? null;
        $createdAt = $formData['created_at'] ?? null;

        // Update existing record
        if (isset($this->id)) {
            if ($isScheduled && !empty($createdAt) && strtotime($createdAt) !== false) {
                $this->created_at = date('Y-m-d H:i:s', strtotime($createdAt));
            }
            $this->show_post_url = 0;
            $this->updated_at = dateTimeNow();

            if (!empty($formData['publish_post'])) {
                $this->status = 1;
            }

            if (!empty($isScheduled) && !empty($scheduledAt)) {
                $this->is_scheduled = 1;
                $this->scheduled_at = $scheduledAt;
            } else {
                $this->is_scheduled = 0;
                $this->scheduled_at = null;
            }

        } else {
            // Insert new record
            if (!empty($isDraft)) {
                $this->status = 0;
            }

            if (!empty($createdAt) && strtotime($createdAt) !== false) {
                $this->created_at = date('Y-m-d H:i:s', strtotime($createdAt));
            }
        }
    }

    /**
     * Handles video embed code, extracts the src from iframe or validates the raw URL
     */
    private function handleVideoEmbedCode(array $formData): void
    {
        $embedInput = (string)($formData['video_embed_code'] ?? '');
        $videoSrc = '';

        if (trim($embedInput) !== '') {
            if (str_contains(strtolower($embedInput), '<iframe')) {
                $videoSrc = extractIframeSrcWithDom($embedInput) ?? '';
            } elseif (filter_var($embedInput, FILTER_VALIDATE_URL)) {
                $videoSrc = trim($embedInput);
            }
        }

        $this->video_embed_code = $videoSrc;
    }
}