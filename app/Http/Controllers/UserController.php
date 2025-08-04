<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function getAllUsers(){
        $users = User::all();

        return response()->json([
            "data" => $users
        ]);
    }

    public function show($id){
        $user = User::findOrFail($id);

        return response()->json([
            'data' => $user
        ]);
    }

    public function store(Request $request){
        $val = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role' => ['required', Rule::in(['admin','bank','kantin','bc','siswa'])]
        ]);

        if($val->fails()){
            return response()->json([
                'error' => $val->errors()
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
        ]);

        return response()->json([
            'message' => 'User created susccessfully',
            'data' => $user
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:100',
            'username' => ['sometimes', Rule::unique('users')->ignore($user->id)],
            'email' => ['sometimes','email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|min:8',
            'role' => ['sometimes', Rule::in(['admin','bank','kantin','siswa'])]
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Update manual
        $data = $request->only(['name', 'username', 'email', 'role']);
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'User updated',
            'data' => $user
        ]);        
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }
}
