<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'price',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at'
    ];
    use HasFactory;
}
