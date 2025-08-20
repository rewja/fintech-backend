<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // siapa yang melakukan transaksi
            $table->foreignId('to_user_id')->nullable()->constrained('users')->onDelete('cascade'); // penerima (seller / siswa / dll)
            $table->enum('type', ['topup', 'withdraw', 'purchase']);
            $table->enum('source', ['bank', 'self'])->default('bank'); // topup sumbernya bank/self
            $table->integer('amount');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
