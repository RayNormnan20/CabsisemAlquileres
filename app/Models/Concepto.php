<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concepto extends Model
{
    use HasFactory;

    protected $table = 'conceptos';

    protected $fillable = [
        'nombre',
        'tipo'
    ];

    protected $casts = [
        'tipo' => 'string'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function concepto()
    {
        return $this->belongsTo(Concepto::class, 'concepto_id');
    }
}
