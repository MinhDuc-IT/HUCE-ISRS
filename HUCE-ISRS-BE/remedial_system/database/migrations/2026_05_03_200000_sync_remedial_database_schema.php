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
        // 1. Semester
        Schema::create('Semester', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Name');
            $table->integer('Year');
            $table->integer('TermNumber');
            $table->boolean('IsActive')->default(false);
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->useCurrent()->useCurrentOnUpdate();
        });

        // 2. TutoringTerm
        Schema::create('TutoringTerm', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('SemesterId');
            $table->string('Name');
            $table->date('StartDate')->nullable();
            $table->date('EndDate')->nullable();
            $table->integer('HeSoPD')->default(1);
            $table->integer('DonGia1Tiet')->default(150000);
            $table->float('HeSoDonGia')->default(1.0);
            $table->boolean('IsDefault')->default(false);
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('SemesterId')->references('Id')->on('Semester')->onDelete('cascade');
        });

        // 3. Department
        Schema::create('Department', function (Blueprint $table) {
            $table->id('Id');
            $table->string('DepartmentCode')->unique();
            $table->string('Name');
            $table->string('Email')->nullable();
            $table->string('Phone')->nullable();
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->useCurrent()->useCurrentOnUpdate();
        });

        // 4. Course
        Schema::create('Course', function (Blueprint $table) {
            $table->id('Id');
            $table->string('CourseCode')->unique();
            $table->string('CourseName');
            $table->integer('Credits');
            $table->unsignedBigInteger('DepartmentId');
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('DepartmentId')->references('Id')->on('Department');
        });

        // 5. Student
        Schema::create('Student', function (Blueprint $table) {
            $table->id('Id');
            $table->string('StudentCode')->unique();
            $table->string('FullName');
            $table->string('Email')->nullable();
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->useCurrent()->useCurrentOnUpdate();
        });

        // 6. Teacher
        Schema::create('Teacher', function (Blueprint $table) {
            $table->id('Id');
            $table->string('TeacherCode')->unique();
            $table->string('FullName');
            $table->unsignedBigInteger('DepartmentId');
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('DepartmentId')->references('Id')->on('Department');
        });

        // 7. TutoringClass
        Schema::create('TutoringClass', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('CourseId');
            $table->unsignedBigInteger('TutoringTermId');
            $table->unsignedBigInteger('TeacherId')->nullable();
            $table->integer('MaxStudents')->default(30);
            $table->integer('CurrentStudents')->default(0);
            $table->string('Status')->default('open');
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('CourseId')->references('Id')->on('Course');
            $table->foreign('TutoringTermId')->references('Id')->on('TutoringTerm');
            $table->foreign('TeacherId')->references('Id')->on('Teacher');
        });

        // 8. TutoringRequest
        Schema::create('TutoringRequest', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('StudentId');
            $table->unsignedBigInteger('CourseId');
            $table->unsignedBigInteger('TutoringTermId');
            $table->string('Status')->default('pending');
            $table->text('Note')->nullable();
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('StudentId')->references('Id')->on('Student');
            $table->foreign('CourseId')->references('Id')->on('Course');
            $table->foreign('TutoringTermId')->references('Id')->on('TutoringTerm');
        });

        // 9. Enrollment
        Schema::create('Enrollment', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('TutoringClassId');
            $table->unsignedBigInteger('StudentId');
            $table->string('Status')->default('enrolled');
            $table->timestamp('EnrolledAt')->useCurrent();
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('TutoringClassId')->references('Id')->on('TutoringClass')->onDelete('cascade');
            $table->foreign('StudentId')->references('Id')->on('Student');
        });

        // 10. ClassSchedule
        Schema::create('ClassSchedule', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('TutoringClassId');
            $table->integer('DayOfWeek');
            $table->string('StartTime');
            $table->string('EndTime');
            $table->string('Room')->nullable();
            $table->string('Status')->nullable();
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('TutoringClassId')->references('Id')->on('TutoringClass')->onDelete('cascade');
        });

        // 11. Attendance
        Schema::create('Attendance', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('EnrollmentId');
            $table->date('StudyDate');
            $table->boolean('IsPresent')->default(false);
            $table->string('Note')->nullable();
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('EnrollmentId')->references('Id')->on('Enrollment')->onDelete('cascade');
        });

        // 12. Payment
        Schema::create('Payment', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('TeacherId');
            $table->unsignedBigInteger('TutoringTermId');
            $table->float('UnitPrice');
            $table->float('Coefficient');
            $table->integer('TotalPeriods');
            $table->float('Amount');
            $table->string('Status')->default('pending');
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('TeacherId')->references('Id')->on('Teacher');
            $table->foreign('TutoringTermId')->references('Id')->on('TutoringTerm');
        });

        // 13. PaymentDetail
        Schema::create('PaymentDetail', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('PaymentId');
            $table->unsignedBigInteger('TutoringClassId');
            $table->integer('Hours');
            $table->float('Amount');
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('PaymentId')->references('Id')->on('Payment')->onDelete('cascade');
            $table->foreign('TutoringClassId')->references('Id')->on('TutoringClass');
        });

        // 14. SystemConfig
        Schema::create('SystemConfig', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Key')->unique();
            $table->text('Value')->nullable();
            $table->string('Description')->nullable();
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->useCurrent()->useCurrentOnUpdate();
        });

        // 15. personal_access_tokens (Sanctum)
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('SystemConfig');
        Schema::dropIfExists('PaymentDetail');
        Schema::dropIfExists('Payment');
        Schema::dropIfExists('Attendance');
        Schema::dropIfExists('ClassSchedule');
        Schema::dropIfExists('Enrollment');
        Schema::dropIfExists('TutoringRequest');
        Schema::dropIfExists('TutoringClass');
        Schema::dropIfExists('Teacher');
        Schema::dropIfExists('Student');
        Schema::dropIfExists('Course');
        Schema::dropIfExists('Department');
        Schema::dropIfExists('TutoringTerm');
        Schema::dropIfExists('Semester');
    }
};
