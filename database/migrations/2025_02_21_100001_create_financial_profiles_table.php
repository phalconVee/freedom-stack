<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->bigInteger('monthly_gross_income');
            $table->bigInteger('monthly_net_income');
            $table->bigInteger('monthly_expenses_total');
            $table->string('filing_status', 32);
            $table->string('state_of_residence', 2)->nullable();
            $table->integer('target_fire_age')->nullable();
            $table->integer('current_age')->nullable();
            $table->string('risk_tolerance', 32)->default('moderate');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_profiles');
    }
};
