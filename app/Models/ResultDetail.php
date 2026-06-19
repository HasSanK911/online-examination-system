<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultDetail extends Model
{
    protected $fillable = [
        'result_id', 'question_id', 'obtained_marks',
        'max_marks', 'is_correct', 'teacher_feedback', 'evaluated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_correct'     => 'boolean',
            'obtained_marks' => 'decimal:2',
            'max_marks'      => 'decimal:2',
        ];
    }

    public function result()
    {
        return $this->belongsTo(Result::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}
