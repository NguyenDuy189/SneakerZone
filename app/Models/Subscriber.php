<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    use HasFactory;

    // BẮT BUỘC PHẢI CÓ DÒNG NÀY MỚI LƯU ĐƯỢC VÀO DATABASE
    protected $fillable = ['email', 'is_active'];
}
