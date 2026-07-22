<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 180)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('photo')->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->enum('role', ['admin', 'user'])->default('user')->index();
            $table->foreignId('plan_id')->nullable()->index();
            $table->unsignedInteger('word_limit')->default(0);
            $table->unsignedInteger('words_used')->default(0);
            $table->unsignedInteger('words_reserved')->default(0);
            $table->unsignedInteger('image_generations_used')->default(0);
            $table->unsignedInteger('image_generations_reserved')->default(0);
            $table->unsignedInteger('audio_generations_used')->default(0);
            $table->unsignedInteger('audio_generations_reserved')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('plan_activated_at')->nullable();
            $table->timestamp('usage_reset_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
