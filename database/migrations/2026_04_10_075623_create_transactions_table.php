<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('type');
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->integer('amount');
            $table->string('currency', 3);

            $table->foreignId('target_account_id')->nullable()->constrained('accounts')->cascadeOnDelete();
            $table->integer('target_amount')->nullable();
            $table->string('target_currency', 3)->nullable();

            $table->foreignId('category_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
