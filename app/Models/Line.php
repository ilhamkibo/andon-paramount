<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Line extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'created_at'];

    public function plan()
    {
        return $this->hasMany(Plan::class);
    }
}
