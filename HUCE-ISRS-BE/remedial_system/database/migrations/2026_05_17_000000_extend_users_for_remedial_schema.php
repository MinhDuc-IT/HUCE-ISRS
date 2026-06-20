<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('sinh_vien')->after('password');
            });
        }

        if (! Schema::hasColumn('users', 'student_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('student_code')->nullable()->index()->after('role');
            });
        }

        if (! Schema::hasColumn('users', 'department_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('department_id')->nullable()->after('student_code');
            });
        }

        if (! Schema::hasColumn('users', 'is_deleted')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_deleted')->default(false)->after('department_id');
            });
        }

        if (Schema::hasColumn('users', 'department_id') && ! $this->hasDepartmentForeignKey()) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('department_id')
                    ->references('id')
                    ->on('departments')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if ($this->hasDepartmentForeignKey()) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['department_id']);
            });
        }

        if (Schema::hasColumn('users', 'is_deleted')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_deleted');
            });
        }
    }

    private function hasDepartmentForeignKey(): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA foreign_key_list('users')");
            foreach ($rows as $row) {
                if (($row->from ?? null) === 'department_id' && ($row->table ?? null) === 'departments') {
                    return true;
                }
            }

            return false;
        }

        $database = DB::getDatabaseName();
        $rows = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', 'users')
            ->where('COLUMN_NAME', 'department_id')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->get();

        return $rows->isNotEmpty();
    }
};
