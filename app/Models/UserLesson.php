<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLesson extends Model
{
    use HasFactory;

    protected $table = 'user_lesson';
    protected $guarded = [];

    public function results()
    {
        return $this->hasMany(Result::class, 'user_lesson_id');
    }
}
