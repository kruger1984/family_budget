<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('categories')
                  ->cascadeOnDelete();
            $table->foreignId('family_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['name', 'family_id', 'parent_id'], 'cat_family_parent_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
