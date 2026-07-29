<?php

namespace App\Models;

class LanguageTranslationModel extends BaseModel
{
    protected $table = 'language_translations';
    protected $allowedFields = ['lang_id', 'label', 'translation'];
    protected $cacheGroup = 'app';

    /**
     * Creates a new translation
     */
    public function create(array $data): bool
    {
        return (bool)$this->insert($data);
    }

    /**
     * Updates a translation
     */
    public function updateTranslation(array $data): bool
    {
        if (empty($data['id']) || !isset($data['translation'])) {
            return false;
        }

        return (bool)$this->update($data['id'], ['translation' => $data['translation']]);
    }

    /**
     * Finds all translations by language ID
     */
    public function findAllByLangId(int $langId): ?array
    {
        return $this->where('lang_id', $langId)
            ->orderBy('label', 'ASC')
            ->findAll();
    }

    /**
     * Finds all translations by label
     */
    public function findAllByLabel(string $label): ?array
    {
        $array = [];

        $rows = $this->where('label', cleanStr($label))
            ->findAll();

        if (empty($rows)) {
            return $array;
        }

        foreach ($rows as $row) {
            $array[$row->lang_id] = $row->translation;
        }

        return $array;
    }

    /**
     * Finds a translation by label
     */
    public function findByLabel(string $label, int $langId): ?object
    {
        return $this->where('label', cleanStr($label))
            ->where('lang_id', $langId)
            ->first();
    }

    /**
     * Finds, filters, and paginates all translations by language ID
     */
    public function findAllPaginated(int $langId, array $filters, int $perPage): array
    {
        $builder = $this->select($this->table . '.*')
            ->where('lang_id', $langId);

        if (!empty($filters['q'])) {
            $builder->groupStart()
                ->like('label', $filters['q'])->orLike('translation', $filters['q'])
                ->groupEnd();
        }

        return $builder->orderBy('label', 'ASC')
            ->paginate($perPage);
    }
}