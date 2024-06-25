<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationName extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function operation_time()
    {
        return $this->hasMany(OperationTime::class, 'name_id');
    }
}
