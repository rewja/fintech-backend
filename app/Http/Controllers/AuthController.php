<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $val = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed'
        ]);

        if ($val->fails()) {
            return response()->json([
                'message' => 'Invalid Fields',
                'errors' => $val->errors()
            ], 422);
        }

        $student = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'student',
            'balance' => 0 
        ]);

        $token = $student->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Register Success',
            'data' => [
                'user' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'username' => $student->username,
                    'email' => $student->email,
                    'role' => $student->role,
                    'balance' => $student->balance
                ]
            ],
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $val = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
        ]);

        if ($val->fails()) {
            return response()->json([
                'message' => 'Invalid Fields',
                'errors' => $val->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('username', 'password'))) {
            return response()->json([
                'message' => 'Wrong username or password'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Login Successfully',
            'data' => [
                'user' => [
                    'id' =>  $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'balance' => $user->balance
                ]
            ],
            'token' => $token
        ], 200);
    }

    public function logout()
    {
        $user = Auth::user();
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Logout Successfully'
        ], 200);
    }

    public function me()
    {
        $user = Auth::user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'balance' => $user->balance
            ]
        ], 200);
    }
}
