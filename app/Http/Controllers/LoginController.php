<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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

    public function checkUserAdmin()
    {
        if (Auth::check()) {
            return response()->json(['is_amdin' => Auth::user()->isAdmin(), 'user' => Auth::user()], 200);
        } else {
            return response()->json(['user_not_logged' => true]);
        }
    }
}
