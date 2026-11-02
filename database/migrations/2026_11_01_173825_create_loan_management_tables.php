<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('username')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('loan_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number', 20)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('loan_type', 20);
            $table->decimal('amount', 12, 2);
            $table->decimal('approved_amount', 12, 2)->nullable();
            $table->unsignedSmallInteger('term_months')->default(6);
            $table->string('purpose');
            $table->string('status', 20)->default('pending');
            $table->decimal('interest_rate', 5, 2)->default(8);
            $table->decimal('total_interest', 12, 2)->nullable();
            $table->decimal('total_payable', 12, 2)->nullable();
            $table->decimal('monthly_amortization', 12, 2)->nullable();
            $table->decimal('outstanding_balance', 12, 2)->nullable();
            $table->string('repayment_status', 20)->nullable();
            $table->text('admin_remarks')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 20)->unique();
            $table->foreignId('loan_application_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('reference_number', 80);
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 30)->default('gcash');
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('action', 60);
            $table->text('description')->default('');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_ledgers');
        Schema::dropIfExists('loan_payments');
        Schema::dropIfExists('loan_applications');
        Schema::dropIfExists('admins');
    }
};
