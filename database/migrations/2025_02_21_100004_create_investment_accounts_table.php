<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type', 32);
            $table->string('name');
            $table->bigInteger('balance');
            $table->bigInteger('monthly_contribution')->default(0);
            $table->decimal('employer_match_pct', 5, 2)->nullable();
            $table->bigInteger('employer_match_limit')->nullable();
            $table->decimal('estimated_annual_return', 5, 2)->default(7.00);
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_accounts');
    }
};
