<?php

namespace App\Models;

class EmailBlacklistModel extends BaseModel
{
    protected $table = 'email_blacklist';
    protected $allowedFields = ['email'];

    protected $validationRules = [
        'email' => [
            'label' => 'trans:email',
            'rules' => 'required|valid_email|is_unique[email_blacklist.email]',
        ]
    ];

    /**
     * Checks if an email is blocked
     */
    public function isBlacklisted(string $email): bool
    {
        return $this->select('id')->where('email', $email)->first() !== null;
    }

    /**
     * Adds an email to the list
     */
    public function addEmail(?string $email): bool
    {
        if (empty($email)) return false;

        return (bool)$this->insert(['email' => $email]);
    }

    /**
     * Deletes a record by email
     */
    public function deleteByEmail(string $email): bool
    {
        $record = $this->where('email', $email)->first();
        if ($record) {
            return $this->delete($record->id);
        }

        return false;
    }

    /**
     * Finds, filters, and paginates all emails
     */
    public function findAllPaginated(array $filters, int $perPage): array
    {
        if (!empty($filters['email'])) {
            $this->like('email', $filters['email']);
        }

        return $this->orderBy('id', 'DESC')->paginate($perPage);
    }
}