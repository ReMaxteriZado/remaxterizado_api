<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $auth = Auth::user();
            $token =  $auth->createToken('LaravelSanctumAuth')->plainTextToken;

            return response()->json(['user' => $auth, 'token' => $token], 200);
        } else {
            return response()->json(['message' => 'These credentials do not match our records'], 422);
        }
    }

    public function checkUserLogged(Request $request)
    {
        $token = $token = $request->user()->currentAccessToken();
        $user = $token->tokenable;

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user = User::find($user->id);

        if ($user) {
            return response()->json(['user' => $user], 200);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }
}
