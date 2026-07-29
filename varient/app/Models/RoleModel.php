<?php

namespace App\Models;

use App\Entities\Role;

class RoleModel extends BaseModel
{
    protected $table = 'roles';
    protected $allowedFields = ['role_name', 'permissions', 'is_default', 'is_super_admin', 'badge_id'];
    protected $returnType = Role::class;
    protected $cacheGroup = 'app';

    /**
     * Finds and paginates all roles
     */
    public function findAllPaginated(int $perPage): array
    {
        return $this->select('roles.*')
            ->orderBy('id')
            ->paginate($perPage);
    }
}