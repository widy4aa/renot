<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->string('file_path', 255);
            $table->string('file_name', 255);
            $table->integer('file_size')->nullable();
            $table->string('file_mime', 100)->nullable();
            $table->foreignId('replaced_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('replaced_at');

            $table->index('document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};
