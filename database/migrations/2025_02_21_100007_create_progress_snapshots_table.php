<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progress_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('snapshot_date');
            $table->bigInteger('net_worth');
            $table->bigInteger('total_debt');
            $table->bigInteger('total_invested');
            $table->bigInteger('total_savings');
            $table->bigInteger('emergency_fund');
            $table->bigInteger('monthly_income');
            $table->bigInteger('monthly_expenses');
            $table->decimal('savings_rate_pct', 5, 2);
            $table->bigInteger('freedom_number');
            $table->decimal('freedom_pct_achieved', 6, 2);
            $table->integer('estimated_months_to_freedom')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'snapshot_date']);
            $table->unique(['user_id', 'snapshot_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_snapshots');
    }
};
