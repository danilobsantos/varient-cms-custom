<?php

namespace App\Models;

class LanguageModel extends BaseModel
{
    protected $table = 'languages';
    protected $allowedFields = ['name', 'short_form', 'language_code', 'text_direction', 'text_editor_lang', 'status', 'language_order'];
    protected $translationModel;
    protected $cacheGroup = 'app';

    protected $validationRules = [
        'name'          => [
            'label' => 'trans:language_name',
            'rules' => 'required|max_length[255]',
        ],
        'short_form'    => [
            'label' => 'trans:short_form',
            'rules' => 'required|max_length[20]',
        ],
        'language_code' => [
            'language_code' => 'trans:language_code',
            'rules'         => 'required|max_length[20]',
        ]
    ];

    public function __construct()
    {
        parent::__construct();

        $this->translationModel = model('LanguageTranslationModel');
    }

    /**
     * Createa a new language and copy default translations
     */
    public function create(array $data): bool
    {
        $this->db->transStart();

        // Insert the new language
        if (!$this->insert($data)) {
            $this->db->transRollback();
            return false;
        }

        $langId = $this->db->insertID();

        // Fetch the default language
        $langDefault = $this->where('id', (int)$this->config->site_lang)->first() ?? $this->orderBy('id', 'asc')->first();
        if (empty($langDefault)) {
            $this->db->transRollback();
            return false;
        }

        // Get default language translations
        $translations = $this->translationModel->findAllByLangId($langDefault->id);
        if (empty($translations)) {
            $this->db->transComplete();
            return true;
        }

        // Duplicate translations for the new language
        foreach ($translations as $translation) {
            $dataTranslation = [
                'lang_id'     => $langId,
                'label'       => $translation->label,
                'translation' => $translation->translation,
            ];
            $this->translationModel->create($dataTranslation);
        }

        model('SettingsModel')->addDefaultSettings($langId);
        model('PageModel')->addDefaultPages($langId);
        model('WidgetModel')->addDefaultWidgets($langId);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Updates a language
     */
    public function updateLanguage(array $data): bool
    {
        $id = $data['id'] ?? null;
        if (empty($id)) {
            return false;
        }

        $language = $this->find($id);
        if (!$language) {
            return false;
        }

        if ((int)$language->id === (int)$this->config->site_lang && empty($data['status'])) {
            return false;
        }

        return (bool)$this->update($id, $data);
    }

    /**
     * Imports a new language and its translations to the database
     */
    public function importLanguage(array $jsonArray): bool
    {
        if (empty($jsonArray['language']) || !is_array($jsonArray['language'])) {
            return false;
        }

        $languageData = $jsonArray['language'];

        // Get total count of existing languages to determine order
        $count = $this->countAllResults();

        // Prepare new language insert data
        $data = [
            'name'             => $languageData['name'] ?? 'language',
            'short_form'       => $languageData['short_form'] ?? 'en',
            'language_code'    => $languageData['language_code'] ?? 'en',
            'text_direction'   => $languageData['text_direction'] ?? 'ltr',
            'text_editor_lang' => $languageData['text_editor_lang'] ?? 'en',
            'status'           => 1,
            'language_order'   => $count + 1,
        ];

        if (!$this->builder()->insert($data)) {
            return false;
        }

        $insertId = (int)$this->db->insertID();
        if (!$insertId) {
            return false;
        }

        // Add related default data for this language
        try {
            model('SettingsModel')->addDefaultSettings($insertId);
            model('PageModel')->addDefaultPages($insertId);
            model('WidgetModel')->addDefaultWidgets($insertId);
        } catch (\Throwable $e) {
        }

        // Import translations
        if (!empty($jsonArray['translations']) && is_array($jsonArray['translations'])) {
            foreach ($jsonArray['translations'] as $translation) {
                // Skip invalid entries
                if (empty($translation['label'])) {
                    continue;
                }

                $this->translationModel->create([
                    'lang_id'     => $insertId,
                    'label'       => $translation['label'],
                    'translation' => $translation['translation'] ?? '',
                ]);
            }
        }

        return true;
    }

    /**
     * Export a specific language and its translations as an array
     */
    public function exportLanguage(int $langId): ?array
    {
        $language = $this->find($langId);
        if (empty($language)) {
            return null;
        }

        $langObject = (object)[
            'name'             => $language->name,
            'short_form'       => $language->short_form,
            'language_code'    => $language->language_code,
            'text_direction'   => $language->text_direction,
            'text_editor_lang' => $language->text_editor_lang,
        ];

        $translations = $this->translationModel->findAllByLangId($langId);

        // Format translations as an array of objects (label + translation)
        $formattedTranslations = [];
        if (!empty($translations)) {
            foreach ($translations as $item) {
                $label = trim($item->label ?? '');
                if ($label !== '') {
                    $formattedTranslations[] = [
                        'label'       => $label,
                        'translation' => (string)($item->translation ?? ''),
                    ];
                }
            }
        }

        return [
            'language'     => $langObject,
            'translations' => $formattedTranslations,
        ];
    }

    /**
     * Finds languages by their status
     */
    public function findAllByStaus(?int $status = 1): array
    {
        $builder = $this->orderBy('language_order', 'ASC');

        if ($status !== null) {
            $builder->where('status', $status);
        }

        return $builder->findAll();
    }

    /**
     * Finds and paginates all languages
     */
    public function findAllPaginated(int $perPage): array
    {
        return $this->orderBy('id', 'DESC')
            ->paginate($perPage);
    }

    /**
     * Finds by short form
     */
    public function findByShortForm(?string $short): ?object
    {
        return $this->where('short_form', cleanStr($short))
            ->first();
    }

    /**
     * Deletes a language and its related data
     */
    public function deleteLanguage(int $id): bool
    {
        $language = $this->find($id);
        if (!$language) {
            return false;
        }

        // Prevent deleting the default site language
        if ((int)$this->config->site_lang === (int)$language->id) {
            return false;
        }

        $this->db->transStart();

        // Delete related records
        model('LanguageTranslationModel')->where('lang_id', $language->id)->delete();
        model('SettingsModel')->where('lang_id', $language->id)->delete();
        model('PageModel')->where('lang_id', $language->id)->delete();
        model('WidgetModel')->where('lang_id', $language->id)->delete();

        // Delete the language itself
        $this->delete($language->id);

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
