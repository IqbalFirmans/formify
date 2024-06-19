<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required|min:5'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid fields',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($validator->validate())) {
            return response()->json([
                'message' => 'Email or password incorrect'
            ], 401);
        }

        $token = Auth::user()->createToken('access_token')->plainTextToken;

        return response()->json(['user' => Auth::user(), 'accessToken' => $token]);

    }

    public function logout(Request $request)
    {
        if (!Auth::guard('sanctum')->check()) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        Auth::guard('sanctum')->user()->tokens()->delete();

        return response()->json(['message' => 'Logout success']);
    }
}
