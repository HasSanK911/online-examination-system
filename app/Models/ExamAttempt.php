<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExamAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id', 'student_id', 'started_at', 'submitted_at',
        'status', 'ip_address', 'user_agent',
        'tab_switch_count', 'suspicious_activity_count',
        'time_spent_seconds', 'question_order',
    ];

    protected function casts(): array
    {
        return [
            'started_at'   => 'datetime',
            'submitted_at' => 'datetime',
            'question_order' => 'array',
        ];
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function answers()
    {
        return $this->hasMany(AttemptAnswer::class, 'attempt_id');
    }

    public function result()
    {
        return $this->hasOne(Result::class, 'attempt_id');
    }

    public function getRemainingSecondsAttribute(): int
    {
        $elapsed = now()->diffInSeconds($this->started_at);
        $total   = $this->exam->duration_minutes * 60;
        return max(0, $total - $elapsed);
    }
}
