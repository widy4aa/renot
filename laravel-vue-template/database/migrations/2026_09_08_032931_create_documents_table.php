<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('certification_type_id')->constrained('certification_types')->restrictOnDelete();
            $table->string('certificate_number', 100)->nullable();
            $table->date('implementation_date')->nullable();
            $table->date('issued_date')->nullable();
            $table->date('expiry_date');
            $table->string('file_path', 255)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->integer('file_size')->nullable();
            $table->string('file_mime', 100)->nullable();
            $table->string('status', 30)->default('pending_approval');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('expiry_date');
            $table->index('certification_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
