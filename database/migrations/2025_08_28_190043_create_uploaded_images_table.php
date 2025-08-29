<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('uploaded_images', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('original_name');
            $table->string('url');
            $table->bigInteger('size');
            $table->string('mime_type');
            $table->timestamp('uploaded_at');
            $table->timestamps();
            
            $table->index('file_name');
            $table->index('uploaded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploaded_images');
    }
};
