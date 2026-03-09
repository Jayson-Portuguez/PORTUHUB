<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $casts = [
        'image_urls' => 'array',
        'price' => 'decimal:2',
    ];

    protected $fillable = [
        'name',
        'category',
        'description',
        'price',
        'image_urls',
        'stock',
    ];
}
