<?php

namespace App\Models;

class FollowerModel extends BaseModel
{
    protected $table = 'followers';
    protected $allowedFields = ['following_id', 'follower_id'];

    /**
     * Toggles the follow status between two users
     */
    public function toggleFollow(int $followingId, int $followerId): string
    {
        if ($followingId === $followerId) {
            return 'error';
        }

        $existingFollow = $this->where('following_id', $followingId)->where('follower_id', $followerId)->first();
        if ($existingFollow) {
            $this->delete($existingFollow->id);
            return 'unfollowed';
        }

        $this->insert([
            'following_id' => $followingId,
            'follower_id'  => $followerId
        ]);

        return 'followed';
    }

    /**
     * Checks if a specific relationship exists between two users
     */
    public function isUserFollows(int $followingId, int $followerId): bool
    {
        return $this->select('id')
                ->where('following_id', $followingId)
                ->where('follower_id', $followerId)
                ->asArray()
                ->first() !== null;
    }

    /**
     * Retrieves the list of users who are following a specific user
     */
    public function getFollowers(int $followingId): array
    {
        return $this->select('users.*')
            ->join('users', 'users.id = followers.follower_id')
            ->where('followers.following_id', $followingId)
            ->findAll();
    }

    /**
     * Retrieves the list of users that a specific user is following
     */
    public function getFollowing(int $followerId): array
    {
        return $this->select('users.*')
            ->join('users', 'users.id = followers.following_id')
            ->where('followers.follower_id', $followerId)
            ->findAll();
    }
}