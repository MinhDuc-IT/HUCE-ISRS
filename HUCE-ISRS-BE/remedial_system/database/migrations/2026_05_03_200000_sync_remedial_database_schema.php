<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('department_code');
            $table->string('faculty_code');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->timestamps();
            $table->boolean('is_deleted')->default(false);
        });

        Schema::create('remedial_terms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('year');
            $table->integer('semester');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->dateTime('registration_start');
            $table->dateTime('registration_end');
            $table->integer('remedial_coefficient')->nullable();
            $table->integer('price_per_period')->nullable();
            $table->integer('price_coefficient')->nullable();
            $table->boolean('is_current_term')->default(false);
            $table->timestamps();
            $table->boolean('is_deleted')->default(false);
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('subject_code');
            $table->string('name');
            $table->integer('credits')->nullable();
            $table->timestamps();
            $table->boolean('is_deleted')->default(false);
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_code')->unique();
            $table->string('full_name')->nullable();
            $table->string('email');
            $table->timestamps();
            $table->boolean('is_deleted')->default(false);
        });

        Schema::create('remedial_registrations', function (Blueprint $table) {
            $table->id();
            $table->integer('remedial_periods');
            $table->dateTime('registration_date');
            $table->string('lecture_name')->nullable();
            $table->string('lecturer_phone_number')->nullable();
            $table->string('lecturer_emal')->nullable();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('remedial_term_id')->constrained('remedial_terms')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->timestamps();
            $table->boolean('is_deleted')->default(false);
        });

        Schema::create('system_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->boolean('is_deleted')->default(false);
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('system_configurations');
        Schema::dropIfExists('remedial_registrations');
        Schema::dropIfExists('students');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('remedial_terms');
        Schema::dropIfExists('departments');
    }
};
