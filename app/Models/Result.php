<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_id', 'student_id', 'exam_id',
        'total_marks', 'obtained_marks', 'percentage',
        'grade', 'gpa', 'is_pass',
        'class_rank', 'department_rank', 'semester_rank',
        'needs_evaluation', 'evaluated_at', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'evaluated_at'     => 'datetime',
            'published_at'     => 'datetime',
            'is_pass'          => 'boolean',
            'needs_evaluation' => 'boolean',
            'total_marks'      => 'decimal:2',
            'obtained_marks'   => 'decimal:2',
            'percentage'       => 'decimal:2',
            'gpa'              => 'decimal:2',
        ];
    }

    public function attempt()
    {
        return $this->belongsTo(ExamAttempt::class, 'attempt_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function details()
    {
        return $this->hasMany(ResultDetail::class);
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }
}
