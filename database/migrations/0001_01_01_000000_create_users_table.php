<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('borrower_id', 20)->unique();
            $table->string('name', 150);
            $table->date('date_of_birth');
            $table->string('phone_number', 20);
            $table->string('email')->unique();
            $table->text('address');
            $table->string('id_type', 30);
            $table->string('id_number', 60);
            $table->string('employment_status', 30);
            $table->decimal('monthly_income', 12, 2)->default(0);
            $table->string('emergency_contact_name', 150);
            $table->string('emergency_contact_relationship', 60);
            $table->string('emergency_contact_phone', 20);
            $table->string('loan_type', 20);
            $table->decimal('credits', 12, 2)->default(0);
            $table->decimal('balance_owed', 12, 2)->default(0);
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->unique(['id_type', 'id_number']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
