<?php

namespace App\Models;

class NewsletterModel extends BaseModel
{
    protected $table = 'newsletter';
    protected $allowedFields = ['email', 'token', 'created_at'];
    protected $createdField = 'created_at';

    protected $validationRules = [
        'email' => [
            'label' => 'trans:email',
            'rules' => 'required|valid_email|max_length[255]',
        ]
    ];

    /**
     * Create a new email to the database and generates a secure token
     */
    public function create(?string $email): bool
    {
        if (empty($email)) {
            return false;
        }

        return (bool)$this->insert([
            'email' => $email,
            'token' => generateSecureToken()
        ]);
    }

    /**
     * Finds by email
     */
    public function findByEmail(?string $email): ?object
    {
        if (empty($email)) return null;

        return $this->where('email', $email)
            ->first();
    }

    /**
     * Finds by token
     */
    public function findByToken(?string $token): ?object
    {
        if (empty($token)) return null;

        return $this->where('token', $token)
            ->first();
    }

    /**
     * Fetches all emails for export efficiently
     */
    public function getAllForExport(): \CodeIgniter\Database\ResultInterface
    {
        return $this->select('id, email, created_at')
            ->orderBy('id', 'DESC')
            ->get();
    }

    /**
     * Fetches a paginated list of emails, optionally filtered by a search query
     */
    public function loadMore(string $q, int $perPage, int $offset): array
    {
        $q = cleanStr($q);

        $builder = $this->select('id, email, created_at')->orderBy('id');

        if (!empty($q)) {
            $builder->groupStart()->like('email', $q)->groupEnd();
        }

        return $builder->findAll($perPage, $offset);
    }
}