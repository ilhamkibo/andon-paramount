<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['plan_id', 'model', 'created_at'];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
    public function note()
    {
        return $this->hasMany(Note::class);
    }
}
