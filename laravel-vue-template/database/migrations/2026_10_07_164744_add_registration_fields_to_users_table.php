<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('registration_status', 20)->default('approved')->after('is_active');
            $table->text('registration_rejected_reason')->nullable()->after('registration_status');
            $table->timestamp('registered_at')->nullable()->after('registration_rejected_reason');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['registration_status', 'registration_rejected_reason', 'registered_at']);
        });
    }
};
