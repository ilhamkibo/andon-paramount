<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;
    protected $fillable = ['problem', 'reason', 'operator', 'production_id'];

    public function production()
    {
        return $this->belongsTo(Production::class);
    }
}
