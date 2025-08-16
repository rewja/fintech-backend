<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $transactions = Transaction::with(['user', 'toUser', 'details.product'])->latest()->get();
        } elseif ($user->role === 'bank') {
            $transactions = Transaction::with(['user', 'toUser', 'details.product'])
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)->orWhere('to_user_id', $user->id);
                })->whereIn('type', ['topup', 'withdraw'])->latest()->get();
        } elseif (in_array($user->role, ['kantin', 'bc'])) {
            $transactions = Transaction::with(['user', 'details.product'])->whereHas('details.product', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            })->latest()->get();
        } else {
            $transactions = Transaction::with(['user', 'toUser', 'details.product'])->whereHas(function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('to_user_id', $user->id);
            })->latest->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    public function daily()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $query = Transaction::with(['user', 'toUser', 'details.product'])->whereDate('created_at', $today);

        if ($user->role === 'bank') {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhere('to_user_id', $user->id);
            })->whereIn('type', ['topup', 'withdraw']);
        } elseif (in_array($user->role, ['kantin', 'bc'])) {
            $query->whereHas('details.product', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            });
        } elseif ($user->role === 'siswa') {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('to_user_id', $user->id);
            });
        }

        $transactions = $query->latest()->get();

        return response()->json([
            'status' => 'success',
            'date' => $today->toDateString(),
            'total_transaction' => $transactions->count,
            'total_amount' => $transactions->sum('amount'),
            'data' => $transactions
        ]);
    }

    public function user($id)
    {
        $auth = Auth::user();
        if ($auth->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'forbidden'
            ]);
        }

        $transactions = Transaction::with(['user', 'toUser', 'details.product'])->where('user_id', $id)->orWhere('to_user_id', $id)->latest()->get();

        return response()->json([
            'status' => 'success',
            'user_id' =>  $id,
            'transaction' => $transactions
        ]);
    }

    public function me()
    {
        $user = Auth::user();

        $transactions = Transaction::with(['user', 'toUser', 'details.product'])->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhere('to_user_id', $user->id)->latest()->get();
        });

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'transactions' => $transactions
        ]);
    }
}
