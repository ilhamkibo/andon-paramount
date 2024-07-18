<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BedModels extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'tact_time', 'id', 'setting_time'];

    public function plan()
    {
        return $this->hasMany(Plan::class);
    }
}
