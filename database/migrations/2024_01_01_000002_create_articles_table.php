<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->json('body_web')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('thumbnail')->nullable();
            $table->text('caption_ig')->nullable();
            $table->text('hashtags_ig')->nullable();
            $table->enum('status', ['draft', 'submitted', 'returned', 'approved', 'published'])->default('draft');
            $table->enum('target_platform', ['web', 'ig', 'both'])->default('web');
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('editor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('editor_notes')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('preview_token')->unique();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
