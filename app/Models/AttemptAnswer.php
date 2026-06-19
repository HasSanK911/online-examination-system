<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttemptAnswer extends Model
{
    use HasFactory;
    protected $fillable = [
        'attempt_id', 'question_id', 'selected_option_ids',
        'text_answer', 'is_marked_for_review', 'is_answered', 'saved_at',
    ];

    protected function casts(): array
    {
        return [
            'selected_option_ids'  => 'array',
            'is_marked_for_review' => 'boolean',
            'is_answered'          => 'boolean',
            'saved_at'             => 'datetime',
        ];
    }

    public function attempt()
    {
        return $this->belongsTo(ExamAttempt::class, 'attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
