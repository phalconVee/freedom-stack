<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freedom_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->bigInteger('monthly_expenses_used');
            $table->decimal('withdrawal_rate', 5, 2);
            $table->decimal('expected_return_rate', 5, 2);
            $table->decimal('inflation_rate', 5, 2)->default(3.00);
            $table->bigInteger('freedom_number');
            $table->decimal('years_to_freedom', 6, 2)->nullable();
            $table->decimal('monthly_savings_rate', 5, 2)->nullable();
            $table->json('assumptions')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freedom_calculations');
    }
};
