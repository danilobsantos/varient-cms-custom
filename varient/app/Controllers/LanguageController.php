<?php

namespace App\Controllers;

use App\Services\UploadService;

class LanguageController extends BaseAdminController
{
    protected $languageModel;
    protected $translationModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        checkPermission('settings');

        $this->languageModel = model('LanguageModel');
        $this->translationModel = model('LanguageTranslationModel');
    }

    /**
     * Languages Settings
     */
    public function languageSettings()
    {
        if (isPostMethod()) {
            $postData = $this->request->getPost();
            $submit = inputPost('submit');

            if ($submit == 'import') {
                $uploadService = new UploadService($this->config);

                $jsonArray = $uploadService->uploadLanguageFile('file');

                $this->languageModel->importLanguage($jsonArray) ? setSuccessMessage('operation_completed') : setErrorMessage('msg_error');

                return redirect()->to(adminUrl('language-settings'));
            }

            $success = !empty($postData['id'])
                ? $this->languageModel->updateLanguage($postData)
                : $this->languageModel->create($postData);

            if ($success) {
                setSuccessMessage(!empty($postData['id']) ? 'msg_updated' : 'msg_added');
            } else {
                setErrorMessage('msg_error');
            }

            return redirect()->to(adminUrl('language-settings'));
        }

        return view('admin/language/languages', [
            'title'         => trans("language_settings"),
            'panelSettings' => panelSettings(),
            'languages'     => $this->languageModel->findAllPaginated($this->perPage),
            'pager'         => $this->languageModel->pager
        ]);
    }

    /**
     * Set Default Language
     */
    public function setDefaultLanguage()
    {
        $id = (int)inputPost('lang_id');

        $language = $this->languageModel->find($id);
        if (empty($language) || (int)$language->status !== 1) {
            setErrorMessage("msg_error");
            return redirect()->to(adminUrl('language-settings'));
        }

        model('ConfigModel')->update($this->config->id, ['site_lang' => $language->id]) ? setSuccessMessage('msg_updated') : setErrorMessage('msg_error');

        return redirect()->to(adminUrl('language-settings'));
    }

    /**
     * AJAX Endpoint: Delete Language
     *
     * @method POST
     */
    public function deleteLanguage()
    {
        $id = (int)inputPost('id');

        if ($id === (int)$this->config->site_lang) {
            setErrorMessage("msg_language_delete");
            return jsonResponse(false);
        }

        $this->languageModel->deleteLanguage($id) ? setSuccessMessage('msg_deleted') : setErrorMessage('msg_error');

        return jsonResponse(true);
    }

    /**
     * Translations
     */
    public function translations($id)
    {
        $language = $this->languageModel->find($id);
        if (empty($language)) {
            return redirect()->to(adminUrl('language-settings'));
        }

        $filters = [
            'q' => cleanStr(inputGet('q'))
        ];

        return view('admin/language/translations', [
            'title'          => trans("edit_translations"),
            'panelSettings'  => panelSettings(),
            'language'       => $language,
            'translations'   => $this->translationModel->findAllPaginated($language->id, $filters, $this->perPage),
            'pager'          => $this->translationModel->pager,
            'breadcrumbLink' => ['label' => trans("languages"), 'url' => adminUrl('language-settings')],
            'filterUrl'      => adminUrl('language/translations/' . $language->id) . '?show=60'
        ]);
    }

    /**
     * AJAX Endpoint: Edit Translation
     *
     * @method POST
     */
    public function editTranslation()
    {
        $data = [
            'id'          => (int)inputPost('id'),
            'translation' => inputPost('translation'),
        ];

        $status = $this->translationModel->updateTranslation($data) ? 1 : 0;

        return jsonResponse([
            'status' => $status,
        ]);
    }

    /**
     * Export Language
     */
    public function export()
    {
        $id = (int)inputPost('id');
        if (empty($id)) {
            setErrorMessage('Invalid language ID!', false);
            return redirect()->to(getBackUrl());
        }

        $language = $this->languageModel->find($id);
        if (empty($language)) {
            setErrorMessage('Language not found!', false);
            return redirect()->to(getBackUrl());
        }

        $tempPath = FCPATH . 'uploads/temp/';
        $fileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $language->name) . '.json';
        $filePath = $tempPath . $fileName;

        // Ensure the temp folder exists and is writable
        if (!is_dir($tempPath) || !is_writable($tempPath)) {
            setErrorMessage('"uploads/temp" folder is not writable or missing!', false);
            return redirect()->to(getBackUrl());
        }

        // Remove old JSON files in the temp directory
        foreach (glob($tempPath . '*.json') as $oldFile) {
            @unlink($oldFile);
        }

        // Get language data from the database
        $data = $this->languageModel->exportLanguage($language->id);
        if (empty($data)) {
            setErrorMessage('No language data found to export!', false);
            return redirect()->to(getBackUrl());
        }

        // Encode data as JSON using safeEncode
        $json = safeEncode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if ($json === '' || $json === false) {
            setErrorMessage('Failed to encode JSON!', false);
            return redirect()->to(getBackUrl());
        }

        // Write JSON data to a temporary file
        if (file_put_contents($filePath, $json) === false) {
            setErrorMessage('Failed to write export file!', false);
            return redirect()->to(getBackUrl());
        }

        // Prepare the download response
        $response = \Config\Services::response();
        $response = $response->download($filePath, null);

        // Automatically delete the file after download completes
        if (file_exists($filePath)) {
            register_shutdown_function(static function () use ($filePath) {
                @unlink($filePath);
            });
        }

        return $response;
    }
}
