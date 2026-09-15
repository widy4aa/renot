<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('mail_host', 255);
            $table->unsignedSmallInteger('mail_port')->default(587);
            $table->string('mail_username', 255);
            $table->text('mail_password');
            $table->string('mail_from_address', 255);
            $table->string('mail_from_name', 100);
            $table->boolean('is_active')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_configs');
    }
};
