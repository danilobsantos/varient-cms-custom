<?php

namespace App\Models;

use CodeIgniter\Model;

class SessionModel extends Model
{
    protected $table = 'ci_sessions';
    protected $primaryKey = 'id';

    /**
     * Purges expired sessions
     */
    public function purgeExpired(int $limit = 10000): int
    {
        $sessionConfig = config(\Config\Session::class);

        if (strpos((string)$sessionConfig->driver, 'DatabaseHandler') === false || (int)$sessionConfig->expiration === 0) {
            return 0;
        }

        $expiration = (int)$sessionConfig->expiration;

        $this->where("`timestamp` < (UNIX_TIMESTAMP() - {$expiration})", null, false)
            ->limit($limit)
            ->delete();

        return $this->db->affectedRows();
    }
}