<?php

namespace App\Models;

class ContactModel extends BaseModel
{
    protected $table = 'contact_messages';
    protected $allowedFields = ['name', 'email', 'message', 'created_at'];
    protected $createdField = 'created_at';

    protected $validationRules = [
        'name' => [
            'label' => 'trans:name',
            'rules' => 'required|max_length[255]',
        ],
        'email' => [
            'label' => 'trans:email',
            'rules' => 'required|valid_email|max_length[255]',
        ],
        'message' => [
            'label' => 'trans:message',
            'rules' => 'required|max_length[5000]',
        ],
    ];

    /**
     * Creates a new contact message
     */
    public function create(array $data): bool
    {
        return (bool)$this->insert($data);
    }

    /**
     * Retrieves all contact messages from the database with pagination
     */
    public function findAllPaginated(int $perPage): array
    {
        return $this->orderBy('id DESC')
            ->paginate($perPage);
    }
}