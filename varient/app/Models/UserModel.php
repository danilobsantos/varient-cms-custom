<?php

namespace App\Models;

use App\Entities\User;

class UserModel extends BaseModel
{
    protected $table = 'users';
    protected $allowedFields = ['username', 'slug', 'email', 'email_status', 'auth_token', 'password', 'role_id', 'oauth_provider', 'oauth_uid', 'avatar',
        'storage_avatar', 'cover_image', 'storage_cover', 'social_media_data', 'status', 'about_me', 'show_email_on_profile', 'show_rss_feeds', 'reward_system',
        'balance', 'total_pageviews', 'payout_methods', 'billing_info', 'has_used_free_trial', 'reset_token', 'reset_token_created_at', 'last_login', 'last_seen', 'created_at'];
    protected $returnType = User::class;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected const EMAIL_VERIFIED = 1;
    protected const EMAIL_NOT_VERIFIED = 0;

    /**
     * Generates a unique username
     */
    public function generateUniqueUsername(?string $username): string
    {
        if (empty($username)) {
            $username = 'user-' . bin2hex(random_bytes(3));
        }

        $newUsername = trim($username);
        $counter = 1;

        while ($this->select('id')->where('username', $newUsername)->first()) {
            if ($counter > 3) {
                $newUsername = $username . '-' . bin2hex(random_bytes(3));
                break;
            }

            $newUsername = $username . '-' . $counter;
            $counter++;
        }

        return $newUsername;
    }

    /**
     * Generates a unique slug for a username
     */
    public function generateUniqueSlug(?string $username): string
    {
        if (empty($username)) {
            $username = 'user-' . bin2hex(random_bytes(3));
        }

        $slug = strSlug($username);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->select('id')->where('slug', $slug)->first()) {
            if ($counter > 3) {
                $slug = $originalSlug . '-' . bin2hex(random_bytes(3));
            } else {
                $slug = $originalSlug . '-' . $counter;
            }

            $counter++;
        }

        return $slug;
    }

    /**
     * Update balance by subtracting a specified amount
     */
    public function updateBalance(int $userId, float $amount): bool
    {
        $user = $this->find($userId);
        if (!$user || $amount <= 0) {
            return false;
        }

        $newBalance = max(0, $user->balance - $amount);
        $newBalance = round($newBalance, 10);

        return $this->update($userId, ['balance' => $newBalance]);
    }

    /**
     * Toggles the reward system status for a specific user
     */
    public function toggleRewardSystem(int $userId): bool
    {
        $this->set('reward_system', '!reward_system', false)
            ->where('id', $userId)
            ->update();

        return (bool)$this->db->affectedRows() > 0;
    }

    /**
     * Toggles the ban status for a specific user
     */
    public function toggleUserBan(int $userId): bool
    {
        $this->set('status', '!status', false)
            ->where('id', $userId)
            ->update();

        return (bool)$this->db->affectedRows() > 0;
    }

    /**
     * Changes a user role
     */
    public function changeUserRole(array $data): bool
    {
        if (empty($data['id']) || empty($data['role_id'])) {
            return false;
        }

        $user = $this->find($data['id']);
        if (!$user) {
            return false;
        }

        $role = model('RoleModel')->find($data['role_id']);
        if (!$role) {
            return false;
        }

        return $this->update($user->id, ['role_id' => $role->id]);
    }

    /**
     * Update last seen date
     */
    public function updateLastSeen(int $userId): void
    {
        $this->update($userId, [
            'last_seen' => dateTimeNow()
        ]);
    }

    /**
     * Loads list of users filtered by search query
     */
    public function loadMoreUsers(string $q, int $perPage, int $offset): array
    {
        $q = cleanStr($q);

        $builder = $this->select('id, username, email, created_at')
            ->orderBy('id');

        if (!empty($q)) {
            $builder->groupStart()
                ->like('username', $q)
                ->orLike('email', $q)
                ->groupEnd();
        }

        return $builder->findAll($perPage, $offset);
    }

    /**
     * Finds a user by ID with their role data
     */
    public function findById(int $id): ?object
    {
        return $this->select('users.*, roles.role_name, roles.permissions, roles.is_super_admin')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->where('users.id', $id)
            ->first();
    }

    /**
     * Finds a user by slug with their role data
     */
    public function findBySlug(?string $slug): ?object
    {
        if (empty($slug)) return null;

        return $this->select('users.*, r.role_name, r.permissions, r.is_super_admin, r.badge_id,
        (SELECT plan_id FROM user_subscriptions us WHERE us.user_id = users.id AND us.status = "active" ORDER BY us.id DESC LIMIT 1) AS plan_id')
            ->join('roles r', 'r.id = users.role_id', 'left')
            ->where('users.slug', cleanStr($slug))->first();
    }

    /**
     * Find a user by email with their role data
     */
    public function findByEmail(string $email): ?object
    {
        return $this->select('users.*, roles.role_name, roles.permissions, roles.is_super_admin')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->where('users.email', cleanStr($email))
            ->first();
    }

    /**
     * Finds a user by reset token
     */
    public function findByResetToken(?string $token): ?object
    {
        if (empty($token)) return null;

        return $this->where('users.reset_token', $token)
            ->first();
    }

    /**
     * Finds all users by username
     */
    public function findAllByUsername(?string $q = null, int $limit = 100): array
    {
        $this->select('id, username')
            ->where('status', 1)
            ->orderBy('username');

        if (!empty($q)) {
            $this->groupStart()
                ->like('id', $q)
                ->orLike('username', $q)
                ->groupEnd();
        }

        return $this->get($limit)->getResult();
    }

    /**
     * Fetches user with role permissions
     */
    public function getUserWithRole(int $userId): ?object
    {
        return $this->join('roles r', 'r.id = users.role_id', 'left')
            ->select('users.*, r.role_name, r.permissions, r.is_super_admin')
            ->where('users.id', $userId)
            ->first();
    }

    /**
     * Generates a secure token based on token type
     */
    public function generateResetToken(int $userId, string $type): ?string
    {
        $prefix = match ($type) {
            'verify' => 'verify_',
            'reset' => 'reset_',
            default => 'token_'
        };

        $tokenWithPrefix = $prefix . generateSecureToken();

        $data = [
            'reset_token'            => $tokenWithPrefix,
            'reset_token_created_at' => dateTimeNow(),
        ];

        if ($this->update($userId, $data)) {
            return $tokenWithPrefix;
        }

        return null;
    }

    /**
     * Verifys user email
     */
    public function verifyUserEmail(int $userId): bool
    {
        return $this->update($userId, [
            'email_status' => self::EMAIL_VERIFIED,
            'reset_token'  => null
        ]);
    }

    /**
     * Resets user email verification
     */
    public function resetEmailVerification(int $userId): bool
    {
        return $this->update($userId, [
            'email_status' => self::EMAIL_NOT_VERIFIED
        ]);
    }

    /**
     * Finds, filters, and paginates user earnings
     */
    public function findAllEarningsPaginated(array $filters, int $perPage): array
    {
        $builder = $this->select('users.*');
        $builder->groupStart()
            ->where('reward_system', 1)
            ->orWhere('balance >', 0)
            ->groupEnd();

        if (!empty($filters['q'])) {
            $builder->groupStart()
                ->like('users.username', $filters['q'])
                ->orLike('users.email', $filters['q'])
                ->groupEnd();
        }

        return $builder->orderBy('users.balance DESC')
            ->paginate($perPage);
    }

    /**
     * Finds, filters, and paginates all users
     */
    public function findAllPaginated(array $filters, int $perPage, string $listType): array
    {
        $selectQuery = 'users.*, r.role_name AS role_name_data, r.permissions, r.is_super_admin, r.badge_id, 
        (SELECT plan_id FROM user_subscriptions WHERE user_id = users.id AND status = "active" ORDER BY id DESC LIMIT 1) AS active_plan_id';

        $builder = $this->select($selectQuery, false);

        $builder->join('roles r', 'r.id = users.role_id', 'left');

        if ($listType === 'premium') {
            $subQuery = '(SELECT user_id FROM user_subscriptions WHERE status = "active")';
            $builder->where("users.id IN {$subQuery}", null, false);
        }

        if ($filters['id'] !== null && $filters['id'] > 0) {
            $builder->where('users.id', (int)$filters['id']);
        }
        if ($filters['status'] !== null) {
            $builder->where('users.status', (int)$filters['status']);
        }
        if ($filters['email_status'] !== null) {
            $builder->where('users.email_status', (int)$filters['email_status']);
        }
        if ($filters['reward_system'] !== null) {
            $builder->where('users.reward_system', (int)$filters['reward_system']);
        }
        if (!empty($filters['role_id'])) {
            $builder->where('users.role_id', (int)$filters['role_id']);
        }
        if (!empty($filters['q'])) {
            $builder->groupStart()
                ->like('username', $filters['q'])
                ->orLike('email', $filters['q'])
                ->groupEnd();
        }

        return $builder->orderBy('users.id DESC')
            ->paginate($perPage);
    }

    /**
     * Deletes users, their associated data, and physical files
     */
    public function deleteUsers(array $userIds, bool $allowSelfDelete = false): bool
    {
        $userIds = array_filter(array_map('intval', $userIds));
        if (empty($userIds)) {
            return false;
        }

        $users = $this->whereIn('id', $userIds)->findAll();
        if (empty($users)) {
            return false;
        }

        $validUserIds = [];
        $filesToDelete = [];
        $currentUserId = user()->id;

        foreach ($users as $user) {

            // Security: Self deletetion
            if (!$allowSelfDelete && (int)$user->id === (int)$currentUserId) {
                continue;
            }

            // Security: Prevent deleting Super Admin if not Super Admin
            if ((int)$user->is_super_admin === 1 && !isSuperAdmin()) {
                continue;
            }

            $validUserIds[] = $user->id;

            // Queue files for deletion
            $filesToDelete[] = (object)[
                'avatar'         => $user->avatar ?? '',
                'storage_avatar' => $user->storage_avatar ?? '',
                'cover'          => $user->cover_image ?? '',
                'storage_cover'  => $user->storage_cover ?? '',
            ];
        }

        if (empty($validUserIds)) {
            return false;
        }

        $this->db->transBegin();

        try {
            $postModel = model('PostModel');
            $imageModel = model('ImageModel');
            $postItemImageModel = model('PostItemImageModel');
            $mediaModel = model('MediaModel');

            $userPosts = $postModel->select('id')->whereIn('user_id', $validUserIds)->findAll();
            $userImages = $imageModel->select('id')->whereIn('user_id', $validUserIds)->findAll();
            $userPostItemImages = $postItemImageModel->select('id')->whereIn('user_id', $validUserIds)->findAll();
            $userMediaFiles = $mediaModel->select('id')->whereIn('user_id', $validUserIds)->findAll();

            if (!empty($userPosts)) {
                $postIds = array_column($userPosts, 'id');

                if (!$postModel->deletePosts($postIds)) {
                    $this->db->transRollback();
                    return false;
                }
            }

            // Batch Delete User Interactions
            $tablesToClean = ['comments', 'reading_lists', 'post_poll_votes', 'poll_votes'];
            foreach ($tablesToClean as $table) {
                $this->db->table($table)->whereIn('user_id', $validUserIds)->delete();
            }

            // Delete followers and following relations
            $this->db->table('followers')
                ->groupStart()
                ->whereIn('follower_id', $validUserIds)->orWhereIn('following_id', $validUserIds)
                ->groupEnd()
                ->delete();

            // Delete images uploaded by user
            if (!empty($userImages)) {
                foreach ($userImages as $userImage) {
                    $imageModel->deleteImage($userImage->id);
                }
            }

            // Delete post item images uploaded by user
            if (!empty($userPostItemImages)) {
                foreach ($userPostItemImages as $userPostItemImage) {
                    $postItemImageModel->deleteImage($userPostItemImage->id);
                }
            }

            // Delete media files uploaded by user
            if (!empty($userMediaFiles)) {
                foreach ($userMediaFiles as $userMedia) {
                    $mediaModel->deleteMedia($userMedia->id);
                }
            }

            $this->whereIn('id', $validUserIds)->delete();

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                return false;
            }

            $this->db->transCommit();

            // Delete physical files
            foreach ($filesToDelete as $file) {
                if (!empty($file->avatar) && !empty($file->storage_avatar)) {
                    deleteStorageFile($file->avatar, $file->storage_avatar);
                }

                if (!empty($file->cover) && !empty($file->storage_cover)) {
                    deleteStorageFile($file->cover, $file->storage_cover);
                }
            }

            return true;

        } catch (\Exception $e) {
            log_message('error', '[UserModel::deleteUsers] Error: ' . $e->getMessage());
            $this->db->transRollback();
            return false;
        }
    }
}