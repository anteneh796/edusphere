<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->string('album', 100)->nullable()->after('caption')->index();
            $table->string('media_type', 20)->default('image')->after('image_path')->index();
            $table->string('video_url', 500)->nullable()->after('media_type');
        });
    }

    public function down(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->dropColumn(['album', 'media_type', 'video_url']);
        });
    }
};
