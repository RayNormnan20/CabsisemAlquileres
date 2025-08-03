<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiaNoLaborable extends Model
{   

    protected $table = 'dias_no_laborables';

    protected $fillable = ['fecha', 'motivo'];
}
