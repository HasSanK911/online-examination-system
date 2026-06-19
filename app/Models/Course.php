<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Course extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = ['department_id', 'code', 'title', 'credit_hours', 'semester', 'description', 'status'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'course_teacher');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'course_student');
    }

    public function questionBanks()
    {
        return $this->hasMany(QuestionBank::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
