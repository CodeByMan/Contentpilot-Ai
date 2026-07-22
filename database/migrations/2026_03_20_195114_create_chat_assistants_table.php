<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_assistants', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120)->unique();
            $table->string('avatar')->nullable();
            $table->string('role_description', 255);
            $table->text('welcome_message');
            $table->text('instructions');
            $table->string('category', 80)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_assistants');
    }
};
