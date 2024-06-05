<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = ['target_quantity', 'bed_models_id', 'date', 'queue'];

    public function line()
    {
        return $this->belongsTo(Line::class);
    }

    public function bed_models()
    {
        return $this->belongsTo(BedModels::class);
    }

    public function production()
    {
        return $this->hasMany(Production::class);
    }
}
