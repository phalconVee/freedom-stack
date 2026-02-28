<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type', 32);
            $table->string('name');
            $table->bigInteger('balance');
            $table->decimal('interest_rate', 5, 2);
            $table->bigInteger('minimum_payment');
            $table->bigInteger('original_balance')->nullable();
            $table->integer('loan_term_months')->nullable();
            $table->boolean('is_federal_student_loan')->default(false);
            $table->string('repayment_plan')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
