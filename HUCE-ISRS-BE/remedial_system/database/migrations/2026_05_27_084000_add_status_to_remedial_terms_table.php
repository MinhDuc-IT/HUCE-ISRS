<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('remedial_terms', function (Blueprint $table) {
            $table->unsignedTinyInteger('status')->default(\App\Domain\Enums\RemedialTermStatus::DRAFT->value)->after('is_deleted')->comment('Trạng thái đợt phụ đạo (0=DRAFT, 1=REGISTRATION_OPEN, 2=ACTIVE, 3=COMPLETED, 4=CANCELLED)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remedial_terms', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
