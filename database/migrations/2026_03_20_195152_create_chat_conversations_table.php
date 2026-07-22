<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('assistant_id')->constrained('chat_assistants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('conversation_uuid');
            $table->text('message');
            $table->longText('response');
            $table->string('model', 80);
            $table->timestamps();
            $table->index(['user_id', 'assistant_id', 'conversation_uuid'], 'chat_owner_conversation_idx');
            $table->index(['conversation_uuid', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
    }
};
