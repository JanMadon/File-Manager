<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileShare extends Model
{
    use HasFactory;

    protected $table = 'starred_files';
    protected $fillable = [
        'file_id',
        'user_id',
        'created_at',
        'updated_at'
    ];

}
