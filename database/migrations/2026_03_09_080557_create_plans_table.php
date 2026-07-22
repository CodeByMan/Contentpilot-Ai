<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100)->unique();
            $table->unsignedInteger('monthly_word_limit');
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedSmallInteger('templates')->default(3);
            $table->unsignedSmallInteger('image_generation_limit')->default(0);
            $table->unsignedSmallInteger('audio_generation_limit')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['plan_id']);
        });

        Schema::dropIfExists('plans');
    }
};
