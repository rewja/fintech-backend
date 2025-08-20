<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BalanceController extends Controller
{
    public function mine() {
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

    public function topup(Request $request) {
        $request->validate([
            'target_user_id' => 'required|exists:users,id',
            'amount' => 'required|integer|min:1',
        ]);

        $bank = Auth::user();
        $target = User::findOrFail($request->target_user_id);

        $target->increment('balance', $request->amount);

        $transaction = Transaction::create([
            'user_id' => $bank->id,
            'to_user_id' => $target->id,
            'type' => 'topup',
            'amount' => $request->amount,
            'source' => 'bank'
        ]);

        return response()->json([
            'message' => 'Balance topped up successfully',
            'data' => [
                'id' => $transaction->id,
                'type' => 'topup',
                'amount' => $request->amount,
                'from_user' => $bank->name,
                'to_user' => $target->name,
                'timestamp' => $transaction->created_at
            ]
        ]);
    }

    public function withdraw(Request $request) {
        $request->validate([
            'target_user_id' => 'required|exists:users,id',
            'amount' => 'required|integer|min:1',
        ]);

        $bank = Auth::user();
        $target = User::findOrFail($request->target_user_id);

        if ($target->balance < $request->amount) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        $target->decrement('balance', $request->amount);

        $transaction = Transaction::create([
            'user_id' => $bank->id,
            'to_user_id' => $target->id,
            'type' => 'withdraw',
            'amount' => $request->amount,
            'source' => 'bank'
        ]);

        return response()->json([
            'message' => 'Withdrawal completed successfully',
            'data' => [
                'id' => $transaction->id,
                'type' => 'withdraw',
                'from_user' => $target->name,
                'by_user' => $bank->name,
                'amount' => $request->amount,
                'timestamp' => $transaction->created_at
            ]
        ]);
    }
}


