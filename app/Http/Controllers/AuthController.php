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
            'name' => 'required',
            'username' => 'required|unique:users,username',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed'
        ]);

        if ($val->fails()) {
            return response()->json([
                'message' => 'Invalid Fields',
                'errors' => $val->errors()
            ], 422);
        }

        $siswa = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'siswa'
        ]);

        $token = $siswa->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Register Success',
            'data' => [
                'user' => [
                    'name' => $siswa->name,
                    'id' =>  $siswa->id,
                    'username' => $siswa->username,
                    'email' => $siswa->email,
                    'role' => $siswa->role
                ]
            ],
            'token' => $token
        ], 200);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('username', 'password'))) {
            return response()->json([
                'message' => 'Wrong username or password'
            ]);
        }

        $user = Auth::user();

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Login Successfully',
            'data' => [
                'user' => [
                    'id' =>  $user->id,
                    'name' => $user->name,
                    'role' => $user->role
                ]
            ],
            'token' => $token
        ], 200);
    }

    public function logout(){
        $user = Auth::user();

        $user->tokens()->delete();

        return response()->json([
            'message' => 'Logout Successfully'
        ]);
    }
}
