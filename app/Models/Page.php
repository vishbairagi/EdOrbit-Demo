<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['title', 'slug', 'blocks', 'status'];

    protected $casts = [
        'blocks' => 'array',
        'status' => 'boolean',
    ];
}
