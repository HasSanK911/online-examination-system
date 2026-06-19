<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'question_bank_id', 'type', 'question_text', 'marks',
        'difficulty', 'tags', 'image_path', 'explanation',
        'correct_answer', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tags'      => 'array',
            'marks'     => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function questionBank()
    {
        return $this->belongsTo(QuestionBank::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function correctOptions()
    {
        return $this->hasMany(QuestionOption::class)->where('is_correct', true);
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_questions')
            ->withPivot(['order', 'marks'])
            ->withTimestamps();
    }

    public function isAutoGraded(): bool
    {
        return in_array($this->type, ['mcq_single', 'mcq_multiple', 'true_false', 'fill_blank']);
    }
}
