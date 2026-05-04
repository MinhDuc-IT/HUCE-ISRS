<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Cập nhật bảng TutoringClass
        Schema::table('TutoringClass', function (Blueprint $table) {
            $table->integer('Status_New')->default(1)->after('Status');
        });

        DB::table('TutoringClass')->where('Status', 'open')->update(['Status_New' => 1]);
        DB::table('TutoringClass')->where('Status', 'full')->update(['Status_New' => 2]);
        DB::table('TutoringClass')->where('Status', 'closed')->update(['Status_New' => 3]);
        DB::table('TutoringClass')->where('Status', 'cancelled')->update(['Status_New' => 0]);

        Schema::table('TutoringClass', function (Blueprint $table) {
            $table->dropColumn('Status');
        });
        Schema::table('TutoringClass', function (Blueprint $table) {
            $table->renameColumn('Status_New', 'Status');
        });

        // 2. Cập nhật bảng TutoringRequest
        Schema::table('TutoringRequest', function (Blueprint $table) {
            $table->integer('Status_New')->default(1)->after('Status');
        });

        DB::table('TutoringRequest')->where('Status', 'pending')->update(['Status_New' => 1]);
        DB::table('TutoringRequest')->where('Status', 'approved')->update(['Status_New' => 2]);
        DB::table('TutoringRequest')->where('Status', 'rejected')->update(['Status_New' => 0]);

        Schema::table('TutoringRequest', function (Blueprint $table) {
            $table->dropColumn('Status');
        });
        Schema::table('TutoringRequest', function (Blueprint $table) {
            $table->renameColumn('Status_New', 'Status');
        });

        // 3. Cập nhật bảng Enrollment
        Schema::table('Enrollment', function (Blueprint $table) {
            $table->integer('Status_New')->default(1)->after('Status');
        });

        DB::table('Enrollment')->where('Status', 'enrolled')->update(['Status_New' => 1]);
        DB::table('Enrollment')->where('Status', 'active')->update(['Status_New' => 2]);
        DB::table('Enrollment')->where('Status', 'cancelled')->update(['Status_New' => 0]);

        Schema::table('Enrollment', function (Blueprint $table) {
            $table->dropColumn('Status');
        });
        Schema::table('Enrollment', function (Blueprint $table) {
            $table->renameColumn('Status_New', 'Status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Phục hồi TutoringClass
        Schema::table('TutoringClass', function (Blueprint $table) {
            $table->string('Status_Old')->default('open')->after('Status');
        });
        DB::table('TutoringClass')->where('Status', 1)->update(['Status_Old' => 'open']);
        DB::table('TutoringClass')->where('Status', 2)->update(['Status_Old' => 'full']);
        DB::table('TutoringClass')->where('Status', 3)->update(['Status_Old' => 'closed']);
        DB::table('TutoringClass')->where('Status', 0)->update(['Status_Old' => 'cancelled']);
        Schema::table('TutoringClass', function (Blueprint $table) {
            $table->dropColumn('Status');
            $table->renameColumn('Status_Old', 'Status');
        });

        // 2. Phục hồi TutoringRequest
        Schema::table('TutoringRequest', function (Blueprint $table) {
            $table->string('Status_Old')->default('pending')->after('Status');
        });
        DB::table('TutoringRequest')->where('Status', 1)->update(['Status_Old' => 'pending']);
        DB::table('TutoringRequest')->where('Status', 2)->update(['Status_Old' => 'approved']);
        DB::table('TutoringRequest')->where('Status', 0)->update(['Status_Old' => 'rejected']);
        Schema::table('TutoringRequest', function (Blueprint $table) {
            $table->dropColumn('Status');
            $table->renameColumn('Status_Old', 'Status');
        });

        // 3. Phục hồi Enrollment
        Schema::table('Enrollment', function (Blueprint $table) {
            $table->string('Status_Old')->default('enrolled')->after('Status');
        });
        DB::table('Enrollment')->where('Status', 1)->update(['Status_Old' => 'enrolled']);
        DB::table('Enrollment')->where('Status', 2)->update(['Status_Old' => 'active']);
        DB::table('Enrollment')->where('Status', 0)->update(['Status_Old' => 'cancelled']);
        Schema::table('Enrollment', function (Blueprint $table) {
            $table->dropColumn('Status');
            $table->renameColumn('Status_Old', 'Status');
        });
    }
};
