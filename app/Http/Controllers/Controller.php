<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $noPermissionMessage = 'No tienes permisos para realizar esta acción';

    public function checkPermission(Int $user_id, String $permission, String $action)
    {
        $user = User::with('role.permissions.permission')->find($user_id);

        if ($user->role->name == "super_admin") {
            return true;
        }

        $permissions = $user->role->permissions;
        $hasPermission = false;

        foreach ($permissions as $p) {
            if ($p->permission->name == $permission && $p->$action == 1) {
                $hasPermission = true;
            }
        }

        return $hasPermission;
    }
}
