<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function checkUserRole(Request $request)
    {
        $user = $this->checkUser($request);
        if ($user) {
            return response()->json(['user' => $user], 200);
        } else {
            return response()->json(['user_not_logged' => true]);
        }
    }

    public function checkUser(Request $request)
    {
        $token = $token = $request->user()->currentAccessToken();
        $user = $token->tokenable;

        if (!$user) {
            return false;
        }

        return User::find($user->id);
    }
}
