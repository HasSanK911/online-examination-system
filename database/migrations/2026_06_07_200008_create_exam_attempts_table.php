<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->dateTime('started_at');
            $table->dateTime('submitted_at')->nullable();
            $table->enum('status', ['in_progress', 'submitted', 'auto_submitted', 'abandoned'])->default('in_progress');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->integer('tab_switch_count')->default(0);
            $table->integer('suspicious_activity_count')->default(0);
            $table->integer('time_spent_seconds')->nullable();
            $table->json('question_order')->nullable();
            $table->timestamps();

            $table->index(['exam_id', 'student_id']);
            $table->index('status');
        });

        Schema::create('attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('exam_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->json('selected_option_ids')->nullable();
            $table->longText('text_answer')->nullable();
            $table->boolean('is_marked_for_review')->default(false);
            $table->boolean('is_answered')->default(false);
            $table->timestamp('saved_at')->nullable();
            $table->timestamps();

            $table->unique(['attempt_id', 'question_id']);
            $table->index('attempt_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempt_answers');
        Schema::dropIfExists('exam_attempts');
    }
};
