<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('remedial_terms', 'created_at')) {
            Schema::table('remedial_terms', function (Blueprint $table) {
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('remedial_terms', 'is_deleted')) {
            Schema::table('remedial_terms', function (Blueprint $table) {
                $table->boolean('is_deleted')->default(false);
            });
        }

        $now = now();
        DB::table('remedial_terms')->whereNull('created_at')->update([
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if (! Schema::hasColumn('remedial_registrations', 'created_at')) {
            Schema::table('remedial_registrations', function (Blueprint $table) {
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('remedial_registrations', 'is_deleted')) {
            Schema::table('remedial_registrations', function (Blueprint $table) {
                $table->boolean('is_deleted')->default(false);
            });
        }

        DB::table('remedial_registrations')->whereNull('created_at')->update([
            'created_at' => DB::raw('registration_date'),
            'updated_at' => DB::raw('registration_date'),
        ]);
    }

    public function down(): void
    {
        Schema::table('remedial_registrations', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at', 'is_deleted']);
        });

        Schema::table('remedial_terms', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at', 'is_deleted']);
        });
    }
};
