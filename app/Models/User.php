<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use HasFactory;

    const ROLE_STUDENT = 1;
    const ROLE_TEACHER = 2;
    const ROLE_ADMIN = 3;

    protected $guarded = [];
    protected $hidden = [
        'password'
    ];
    protected $casts = [
        'password' => 'hashed',
    ];

    public function teacherInfo()
    {
        return $this->hasOne(TeacherInfo::class, 'teacher_id');
    }

    public function lessons()
    {
        return $this->belongsToMany(Lesson::class, 'user_lesson', 'user_id', 'lesson_id');
    }

    public function lessonUsers()
    {
        return $this->hasMany(UserLesson::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'teacher_courses', 'teacher_id', 'course_id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
