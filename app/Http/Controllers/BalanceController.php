<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BalanceController extends Controller
{
    public function mine(){
        $user = Auth::user();

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'balance' => $user->balance,
            ],

            'last_update' => $user->updated_at
        ]);
    }

    public function show($id){
        $user = User::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'balance' => $user->balance,
            ],
            'last_updated' => $user->updated_at
            ]);
    }

    public function topup(Request $request){
        $request->validate([
            'target_user_id' => 'required|exists:users,id',
            'amount' => 'required|integer|min:1',
        ]);

        $bank  = Auth::user();
        $target = User::findOrFail($request->target_user_id);

        $target->balance += $request->amount;
        $target->save();

        $transaction = Transaction::create([
            'user_id' => $bank->id,
            'type' => 'topup',
            'amount' => $request->amount,
            'to_user_id' => $target->id
        ]);

        return response()->json([
            'status' => 'success',
            'messages' => 'Balance has been topped up successfull',
            'from' => $bank->only(['id', 'name', 'role']),
            'to' => $target->only(['id', 'name', 'role', 'balance']),
            'transaction_id' => $transaction->id,
            'created_at' => $transaction->created_at
        ]);
    }

    public function withdraw(Request $request){
        $request->validate([
            'target_user_id' => 'required|exists:users,id',
            'amount' => 'required|integer|min:1',
        ]);

        $bank  = Auth::user();
        $target = User::findOrFail($request->target_user_id);

        if($target->balance < $request->amount){
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient balance for this user'
            ]);
        }

        $target->balance -= $request->amount;
        $target->save();

        $transaction = Transaction::create([
            'user_id' => $bank->id,
            'type' => 'withdraw',
            'amount' => $request->amount,
            'to_user_id' => $target->id
        ]);

        return response()->json([
            'status' => 'success',
            'mesagge' => '',
            'from' => $target->only(['id', 'name', 'role', 'balance']),
            'processed_by' => $bank->only(['id', 'name', 'role']),
            'transaction_id' => $transaction->id,
            'created_at' => $transaction->created_at
        ]);
    }
}
