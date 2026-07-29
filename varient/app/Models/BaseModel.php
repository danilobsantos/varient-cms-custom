<?php

namespace App\Models;

use CodeIgniter\Model;

class BaseModel extends Model
{
    /*
     * Core CodeIgniter Properties
     */
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = true;
    protected $createdField = '';
    protected $updatedField = '';

    /*
     * Global Context & Settings
     */
    public $activeLang;
    public $config;
    public $settings;
    public $activeLanguages;

    /*
     * Validation & Cache Management
     */
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $cacheGroup = null;

    /*
     * Event Callbacks
     * Triggers cache invalidation after any insert, update, or delete operation.
     */
    protected $afterInsert = ['onDataChanged'];
    protected $afterUpdate = ['onDataChanged'];
    protected $afterDelete = ['onDataChanged'];
    protected $afterInsertBatch = ['onDataChanged'];
    protected $afterUpdateBatch = ['onDataChanged'];

    /**
     * BaseModel Constructor. Initializes core context values required across all extended models
     */
    public function __construct()
    {
        parent::__construct();

        $this->config = getContextValue('config');
        $this->settings = getContextValue('settings');
        $this->activeLanguages = getContextValue('activeLanguages');
        $this->activeLang = getContextValue('activeLang');
    }

    /**
     * Safely processes and translates the model's validation rules
     *
     * @param array $options Additional options
     * @return array The processed validation rules with translated labels
     */
    public function getValidationRules(array $options = []): array
    {
        $rules = $this->validationRules;

        foreach ($rules as $field => $settings) {
            if (is_array($settings) && isset($settings['label']) && is_string($settings['label'])) {
                $rules[$field]['label'] = $this->translateLabel($settings['label']);
            }
        }

        return $rules;
    }

    /**
     * Translates a validation label if it contains the designated prefix
     *
     * @param string $label The raw label string (e.g., 'trans:username').
     * @return string The translated label or the original string
     */
    protected function translateLabel(string $label): string
    {
        if (strpos($label, 'trans:') === 0) {
            $key = substr($label, 6);
            return trans($key);
        }

        return $label;
    }

    /**
     * Attempts to save an Entity safely without throwing exceptions for unchanged data
     *
     * @param object|array $entity The entity or data array to be saved
     * @return bool Returns true on success or if no changes were needed
     */
    public function trySave($entity): bool
    {
        if (is_object($entity) && method_exists($entity, 'hasChanged') && !$entity->hasChanged()) {
            return true;
        }

        try {
            return $this->save($entity);
        } catch (\CodeIgniter\Database\Exceptions\DataException $e) {
            return true;
        } catch (\Exception $e) {
            log_message('error', '[BaseModel::trySave] Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Production-grade custom pagination for handling massive datasets
     *
     * @param int $perPage Number of records to display per page
     * @param int $totalCount Pre-calculated total record count
     * @param string $group Pagination group identifier
     * @param bool $triggerModelEvents Whether to trigger 'afterFind' model events
     * @return array|object|null         The paginated result set
     */
    public function customPaginate(int $perPage, int $totalCount, string $group = 'default', bool $triggerModelEvents = false)
    {
        if ($perPage <= 0) {
            $perPage = 20;
        }

        $pager = \Config\Services::pager();

        if ($totalCount === 0) {
            $pager->store($group, 1, $perPage, 0, 0);
            $this->pager = $pager;
            return [];
        }

        $request = \Config\Services::request();

        $pageParam = $request->getVar('page_' . $group) ?? $request->getVar('page') ?? 1;
        $page = max(1, (int)$pageParam);
        $maxPage = max(1, (int)ceil($totalCount / $perPage));

        $page = min($page, $maxPage);
        $offset = ($page - 1) * $perPage;

        $builder = clone $this->builder();

        if ($this->useSoftDeletes && !$this->tempUseSoftDeletes) {
            $builder->where($this->deletedField, null);
        }

        $query = $builder->limit($perPage, $offset)->get();
        $data = $query->getResult($this->returnType);

        if ($triggerModelEvents) {
            $eventData = $this->trigger('afterFind', ['data' => $data]);
            $data = $eventData['data'];
        }

        $pager->store($group, $page, $perPage, $totalCount, 0);
        $this->pager = $pager;

        return $data;
    }

    /**
     * Acts as a central hub for cache invalidation after data modifications
     *
     * @param array $data The event payload
     * @return array The unmodified event payload
     */
    protected function onDataChanged(array $data): array
    {
        // Reset cache files by cache group
        if ($this->cacheGroup !== null) {
            service('cacheService')->reset($this->cacheGroup);
        }

        // Browser cache refresh
        if (in_array($this->table, ['posts', 'categories'])) {
            service('cacheService')->refreshBrowserCacheVersion();
        }

        return $data;
    }
}