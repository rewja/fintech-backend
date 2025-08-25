<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            // admin bisa lihat semua transaksi
            $transactions = Transaction::with(['user', 'toUser', 'details.product'])
                ->latest()->get();
        } else {
            // role lain hanya bisa lihat transaksi terkait dirinya
            $transactions = Transaction::with(['user', 'toUser', 'details.product'])
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhere('to_user_id', $user->id);
                })
                ->latest()->get();
        }

        return response()->json([
            'success' => true,
            'data' => $transactions
        ], 200);
    }

    // GET /transactions/{id}
    public function show($id)
    {
        $user = Auth::user();

        $transaction = Transaction::with(['user', 'toUser', 'details.product'])
            ->findOrFail($id);

        // admin bisa lihat semua
        if ($user->role !== 'admin') {
            // selain admin → hanya boleh akses kalau dia user atau to_user
            if ($transaction->user_id !== $user->id && $transaction->to_user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this transaction'
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $transaction
        ], 200);
    }

    // POST /transactions
    public function store(Request $request)
    {
        $val = Validator::make($request->all(), [
            'type' => 'required|in:purchase,topup',
            'amount' => 'required_if:type,topup,withdraw|integer|min:1',
            'items' => 'array|required_if:type,purchase'
        ]);

        if ($val->fails()) {
            return response()->json(['errors' => $val->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();

            // ✅ SELF TOPUP
            if ($request->type === 'topup') {
                $user->increment('balance', $request->amount);

                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'topup',
                    'amount' => $request->amount,
                    'source' => 'self'
                ]);

                DB::commit();
                return response()->json([
                    'message' => 'Balance topped up successfully',
                    'data' => [
                        'id' => $transaction->id,
                        'type' => 'topup',
                        'amount' => $request->amount,
                        'from_user' => $user->name,
                        'timestamp' => $transaction->created_at
                    ]
                ]);
            }

            // ✅ PURCHASE
            if ($request->type === 'purchase') {
                if (!$request->items) {
                    return response()->json(['message' => 'Items required'], 422);
                }

                $total = 0;
                $itemsData = [];
                $seller = null;

                foreach ($request->items as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $qty = $item['qty'];
                    $subtotal = $product->price * $qty;

                    $total += $subtotal;

                    // tentukan seller otomatis dari product.owner_id
                    if (!$seller) {
                        $seller = $product->owner;
                    } elseif ($seller->id !== $product->owner_id) {
                        return response()->json([
                            'message' => 'All items must belong to the same seller'
                        ], 400);
                    }

                    $itemsData[] = [
                        'product_id' => $product->id,
                        'qty' => $qty,
                        'price' => $product->price,
                        'subtotal' => $subtotal
                    ];
                }

                // cek saldo user
                if ($user->balance < $total) {
                    return response()->json(['message' => 'Insufficient balance'], 400);
                }

                // update saldo
                $user->decrement('balance', $total);
                $seller->increment('balance', $total);

                // simpan transaksi
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'purchase',
                    'amount' => $total,
                    'to_user_id' => $seller->id
                ]);

                foreach ($itemsData as $detail) {
                    $transaction->details()->create($detail);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Purchase transaction successful',
                    'data' => [
                        'transaction_id' => $transaction->id,
                        'buyer' => $user->name,
                        'seller' => $seller->name,
                        'total' => $total,
                        'items' => $transaction->details()->with('product')->get(),
                        'timestamp' => $transaction->created_at
                    ]
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Transaction failed: ' . $e->getMessage()], 500);
        }
    }
}
