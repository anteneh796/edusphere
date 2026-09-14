<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug', 120)->unique();
            $table->string('title', 180);
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('image_path', 255)->nullable();
            $table->foreignUuid('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('published')->default(true)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};