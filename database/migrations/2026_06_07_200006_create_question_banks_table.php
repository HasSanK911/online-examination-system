<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->softDeletes();
            $table->timestamps();

            $table->index('course_id');
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_bank_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['mcq_single', 'mcq_multiple', 'true_false', 'fill_blank', 'short', 'descriptive']);
            $table->longText('question_text');
            $table->decimal('marks', 5, 2)->default(1.00);
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->json('tags')->nullable();
            $table->string('image_path', 500)->nullable();
            $table->text('explanation')->nullable();
            $table->text('correct_answer')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['question_bank_id', 'type', 'difficulty']);
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->text('option_text');
            $table->string('option_image', 500)->nullable();
            $table->boolean('is_correct')->default(false);
            $table->tinyInteger('order')->default(0);
            $table->timestamps();

            $table->index('question_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('question_banks');
    }
};
