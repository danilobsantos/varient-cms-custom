<?php

namespace App\Models;

class EventRegistrationModel extends BaseModel
{
    protected $table = 'event_registrations';
    protected $allowedFields = ['post_id', 'user_id', 'full_name', 'email', 'custom_data'];

    /**
     * Checks if a user or an email is already registered for a specific event
     */
    public function isRegistered(int $postId, ?int $userId = null, ?string $email = null): bool
    {
        $builder = $this->select('id')->where('post_id', $postId);

        if ($userId) {
            $builder->groupStart()
                ->where('user_id', $userId)
                ->orWhere('email', $email)
                ->groupEnd();
        } else {
            $builder->where('email', $email);
        }

        return $builder->first() !== null;
    }

    /**
     * Gets the total number of registrations for a specific event
     */
    public function getRegistrationCount(int $postId): int
    {
        return $this->where('post_id', $postId)->countAllResults();
    }

    /**
     * Adds a new registration to the event
     */
    public function addRegistration(array $data): bool
    {
        if (isset($data['custom_data']) && is_array($data['custom_data'])) {
            $data['custom_data'] = json_encode($data['custom_data']);
        }

        return $this->insert($data) !== false;
    }

    /**
     * Finds, filters, and paginates all participants
     */
    public function findAllPaginated(int $postId, int $perPage): array
    {
        $this->where('post_id', $postId);

        return $this->orderBy('id', 'ASC')
            ->paginate($perPage);
    }
}