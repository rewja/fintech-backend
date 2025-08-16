<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('user, toUser')
            ->where('to_user_id', auth()->id())
            ->orWhere('user_id', auth()->id())
            ->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $transactions
        ], 200);
    }

    public function show($id){
        $transaction = Transaction::with(['user', 'toUser'])->finOrFail($id);

        if($transaction->user_id !== auth()->id() && $transaction->to_user_id !== auth()->id()){
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $transaction
        ], 200);
    }

    public function store(Request $request)
    {
        $val = Validator::make($request->all(), [
            'type' => 'required|in:topup,purchase,withdraw',
            'amount' => 'required|integer|min:1',
            'to_user_id' => 'nullable|exists:users,id'
        ]);

        if($val->fails()){
            return response()->json([
                'success' => false,
                'errors' => $val->errors()
            ], 422);
        }

        DB::beginTransaction();

        try{
            $user = auth()->id();

            if($request->type === 'topup'){
                $user->increment('balance', $request->amount);
            }

            if($request->type === 'withdraw'){
                if($user->saldo < $request->amount){
                    return response()->json([
                        'message' => 'insufficient balance'
                    , 400]);
                }
                $user->decrement('balance', $request->amount);
            }

            if($request->type === 'purchase'){
                if(!$request->to_user_id){
                    return response()->json([
                        'message' => 'Recipient is required for purchase'
                    ], 422);
                }
                if($user->saldo < $request->amount) {
                    return response()->json([
                        'message' => 'insufficient balance'                    
                    ], 400);
                }

                $user->decrement('balance', $request->amount);

                $seller = User::find($request->to_user_id);
                $seller->increment('balace', $request->amount);
            }

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => $user->type,
                'amount' => $user->amount,
                'to_user_id' => $user->to_user_id,
            ]);

            DB::commit();

            return response()->json([
                'success' => false,
                'message' => 'Transactions successful',
                'data' => $transaction->load(['user', 'toUser'])
            ],201);
        } catch (\Exception $e){
            DB::rollback();
            return response()->json([
                'success' => false,
                'mesagges' => 'Transaction failed: ' . $e->getMessage()
            ], 500);
        }

    }
}
