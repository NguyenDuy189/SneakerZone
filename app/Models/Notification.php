<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications'; // Định danh rõ tên bảng

    protected $fillable = [
        'title',
        'message',
        'type',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];
}