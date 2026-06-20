<?php

namespace App\Models;

use App\Models\Concerns\SerializesDatesInAppTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Exam extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, SerializesDatesInAppTimezone;

    protected $fillable = [
        'course_id', 'created_by', 'title', 'description', 'instructions',
        'total_marks', 'passing_marks', 'duration_minutes',
        'start_time', 'end_time', 'status',
        'shuffle_questions', 'shuffle_options', 'allow_backtrack',
        'show_result_immediately', 'max_attempts', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'start_time'              => 'datetime',
            'end_time'                => 'datetime',
            'published_at'            => 'datetime',
            'shuffle_questions'       => 'boolean',
            'shuffle_options'         => 'boolean',
            'allow_backtrack'         => 'boolean',
            'show_result_immediately' => 'boolean',
            'total_marks'             => 'decimal:2',
            'passing_marks'           => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'exam_questions')
            ->withPivot(['order', 'marks'])
            ->withTimestamps()
            ->orderByPivot('order');
    }

    public function attempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function isLive(): bool
    {
        // Attemptable while inside the scheduled window. We don't depend on a
        // background job flipping 'scheduled' → 'active', so both count as live.
        return in_array($this->status, ['scheduled', 'active'], true)
            && now()->between($this->start_time, $this->end_time);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeLive($query)
    {
        return $query->whereIn('status', ['scheduled', 'active'])
            ->where('start_time', '<=', now())
            ->where('end_time', '>', now());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')->where('start_time', '>', now());
    }
}
