<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationTime extends Model
{
    use HasFactory;

    protected $fillable = ['start', 'finish', 'status'];
}
