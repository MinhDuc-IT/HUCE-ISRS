<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('remedial_terms', 'year')) {
            Schema::table('remedial_terms', function (Blueprint $table) {
                $table->integer('year')->nullable()->after('name');
                $table->integer('semester')->nullable()->after('year');
            });
        }

        if (Schema::hasTable('semesters')) {
            $terms = DB::table('remedial_terms')->whereNotNull('semester_id')->get(['id', 'semester_id']);

            foreach ($terms as $term) {
                $semester = DB::table('semesters')->where('id', $term->semester_id)->first(['year', 'term_number']);
                if ($semester) {
                    DB::table('remedial_terms')->where('id', $term->id)->update([
                        'year'     => $semester->year,
                        'semester' => $semester->term_number,
                    ]);
                }
            }
        }

        $defaultYear = (int) date('Y');
        DB::table('remedial_terms')->whereNull('year')->update([
            'year'     => $defaultYear,
            'semester' => 1,
        ]);

        if (Schema::hasColumn('remedial_terms', 'semester_id')) {
            Schema::table('remedial_terms', function (Blueprint $table) {
                $table->dropForeign(['semester_id']);
                $table->dropColumn('semester_id');
            });
        }

        Schema::dropIfExists('semesters');
    }

    public function down(): void
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('year');
            $table->integer('term_number');
            $table->timestamps();
            $table->boolean('is_deleted')->default(false);
        });

        Schema::table('remedial_terms', function (Blueprint $table) {
            $table->foreignId('semester_id')->nullable()->after('is_current_term')->constrained('semesters')->cascadeOnDelete();
        });

        $terms = DB::table('remedial_terms')->get(['id', 'year', 'semester', 'name']);

        foreach ($terms as $term) {
            $semesterId = DB::table('semesters')->insertGetId([
                'name'        => "HK{$term->semester} – {$term->year}",
                'year'        => $term->year,
                'term_number' => $term->semester,
                'created_at'  => now(),
                'updated_at'  => now(),
                'is_deleted'  => false,
            ]);

            DB::table('remedial_terms')->where('id', $term->id)->update(['semester_id' => $semesterId]);
        }

        Schema::table('remedial_terms', function (Blueprint $table) {
            $table->dropColumn(['year', 'semester']);
        });
    }
};
