<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_number', 50)->nullable()->unique()->after('id');
            $table->string('role', 20)->default('pegawai')->after('password');
            $table->string('phone', 20)->nullable()->after('role');
            $table->string('avatar', 255)->nullable()->after('phone');
            $table->foreignId('department_id')->nullable()->after('avatar')->constrained('departments')->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('department_id');
            $table->foreignId('created_by')->nullable()->after('remember_token')->constrained('users')->nullOnDelete();

            $table->index('employee_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['created_by']);
            $table->dropIndex(['employee_number']);
            $table->dropColumn([
                'employee_number',
                'role',
                'phone',
                'avatar',
                'department_id',
                'is_active',
                'created_by',
            ]);
        });
    }
};
