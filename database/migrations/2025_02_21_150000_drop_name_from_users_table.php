<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill first_name/last_name from name for existing users missing them
        DB::table('users')
            ->whereNull('first_name')
            ->whereNotNull('name')
            ->orderBy('id')
            ->chunk(100, function ($users) {
                foreach ($users as $user) {
                    $name = trim((string) $user->name);
                    $space = strpos($name, ' ');
                    $first = $space !== false ? substr($name, 0, $space) : $name;
                    $last = $space !== false ? trim(substr($name, $space)) : '';
                    DB::table('users')->where('id', $user->id)->update([
                        'first_name' => $first ?: null,
                        'last_name' => $last ?: null,
                    ]);
                }
            });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable()->after('last_name');
        });

        DB::table('users')->orderBy('id')->chunk(100, function ($users) {
            foreach ($users as $user) {
                $full = trim(trim((string) ($user->first_name ?? '')) . ' ' . trim((string) ($user->last_name ?? '')));
                if ($full !== '') {
                    DB::table('users')->where('id', $user->id)->update(['name' => $full]);
                }
            }
        });
    }
};
