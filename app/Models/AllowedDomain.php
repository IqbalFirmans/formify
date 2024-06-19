<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllowedDomain extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $hidden = ['id', 'form_id'];   

    public $timestamps = false;
}
