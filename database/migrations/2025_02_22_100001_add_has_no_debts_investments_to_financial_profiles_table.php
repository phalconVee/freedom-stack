<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_profiles', function (Blueprint $table) {
            $table->boolean('has_no_debts')->default(false)->after('risk_tolerance');
            $table->boolean('has_no_investments')->default(false)->after('has_no_debts');
        });
    }

    public function down(): void
    {
        Schema::table('financial_profiles', function (Blueprint $table) {
            $table->dropColumn(['has_no_debts', 'has_no_investments']);
        });
    }
};
