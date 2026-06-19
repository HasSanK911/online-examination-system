<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->unique()->constrained('exam_attempts')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_marks', 8, 2);
            $table->decimal('obtained_marks', 8, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->string('grade', 5)->nullable();
            $table->decimal('gpa', 3, 2)->nullable();
            $table->boolean('is_pass')->default(false);
            $table->integer('class_rank')->nullable();
            $table->integer('department_rank')->nullable();
            $table->integer('semester_rank')->nullable();
            $table->boolean('needs_evaluation')->default(false);
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['exam_id', 'student_id']);
            $table->index(['exam_id', 'obtained_marks']);
            $table->index('published_at');
        });

        Schema::create('result_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->decimal('obtained_marks', 5, 2)->default(0);
            $table->decimal('max_marks', 5, 2)->default(0);
            $table->boolean('is_correct')->nullable();
            $table->text('teacher_feedback')->nullable();
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('result_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_details');
        Schema::dropIfExists('results');
    }
};
