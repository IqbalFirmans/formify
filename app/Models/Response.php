<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $hidden = ['id', 'form_id'];

    public $timestamps = false;

    public function answers()
    {
        return $this->hasMany(Answer::class, 'response_id');
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function responden()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
