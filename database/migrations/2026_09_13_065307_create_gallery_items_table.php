<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('caption', 180)->nullable();
            $table->string('image_path', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('published')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_items');
    }
};