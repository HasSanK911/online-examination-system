<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Student extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id', 'department_id', 'student_id', 'roll_number',
        'semester', 'batch', 'phone', 'address', 'guardian_name',
        'guardian_phone', 'date_of_birth', 'gender', 'profile_photo',
        'status', 'enrollment_date',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth'    => 'date',
            'enrollment_date'  => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_student');
    }

    public function examAttempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function getProfilePhotoUrlAttribute(): string
    {
        return $this->profile_photo
            ? asset('storage/' . $this->profile_photo)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->user->name ?? 'Student') . '&background=6366f1&color=fff';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
