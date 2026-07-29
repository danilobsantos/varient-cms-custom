<?php

namespace App\Models;

class LoginAttemptModel extends BaseModel
{
    protected $table = 'login_attempts';
    protected $allowedFields = ['ip_address', 'email', 'attempts', 'last_attempt'];

    /**
     * Retrieves the login attempt record for a specific IP address
     */
    public function getAttempt(string $ipAddress): ?object
    {
        return $this->where('ip_address', $ipAddress)->first();
    }

    /**
     * Records a failed login attempt
     * Resets the counter if the lockout time has already passed
     */
    public function recordAttempt(string $ipAddress, string $email, int $lockoutTime): void
    {
        $record = $this->getAttempt($ipAddress);
        $now = dateTimeNow();
        $threshold = date('Y-m-d H:i:s', time() - $lockoutTime);

        if ($record) {
            $attempts = ($record->last_attempt < $threshold) ? 1 : $record->attempts + 1;

            $this->update($record->id, [
                'email'        => $email,
                'attempts'     => $attempts,
                'last_attempt' => $now
            ]);
        } else {
            $this->insert([
                'ip_address'   => $ipAddress,
                'email'        => $email,
                'attempts'     => 1,
                'last_attempt' => $now
            ]);
        }
    }

    /**
     * Clears the login attempts for a specific IP (used on successful login)
     */
    public function clearAttempts(string $ipAddress): void
    {
        $this->where('ip_address', $ipAddress)->delete();
    }

    /**
     * Garbage Collection: Deletes expired records to keep the database light
     */
    public function cleanupOldAttempts(int $lockoutTime): void
    {
        $threshold = date('Y-m-d H:i:s', time() - $lockoutTime);
        $this->where('last_attempt <', $threshold)->delete();
    }
}