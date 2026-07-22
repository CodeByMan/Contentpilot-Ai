<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_input_fields', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('template_id')->constrained()->cascadeOnDelete();
            $table->string('key', 100);
            $table->string('title', 160);
            $table->text('description')->nullable();
            $table->enum('type', ['text', 'textarea']);
            $table->boolean('is_required')->default(true);
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
            $table->unique(['template_id', 'key']);
            $table->index(['template_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_input_fields');
    }
};
