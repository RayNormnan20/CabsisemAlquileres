<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oficina extends Model
{
    use HasFactory;

    protected $table = 'oficina';
    protected $primaryKey = 'id_oficina';

    protected $fillable = [
        'nombre',
        'id_moneda',
        'pais',
        'codigo',
        'max_abonos_diarios', // Nuevo campo
        'porcentajes_credito',
        'activar_seguros', // Nuevo campo
        'ver_caja_anterior',
        'ver_entradas_salidas',
        'consultar_cuadre_pasado',
        'cobrador_edita_clientes',
        'cobrador_ingresos_gastos',
        'pedir_base_al_ingresar',
        'liquidar_rutas',
        'foto_documento_obligatoria',
        'cambiar_claves_usuarios',
        'creditos_requieren_autorizacion'
    ];

    protected $casts = [
        'max_abonos_diarios' => 'integer',
        'activar_seguros' => 'boolean',
        'ver_caja_anterior' => 'boolean',
        'ver_entradas_salidas' => 'boolean',
        'consultar_cuadre_pasado' => 'boolean',
        'cobrador_edita_clientes' => 'boolean',
        'cobrador_ingresos_gastos' => 'boolean',
        'pedir_base_al_ingresar' => 'boolean',
        'liquidar_rutas' => 'boolean',
        'foto_documento_obligatoria' => 'boolean',
        'cambiar_claves_usuarios' => 'boolean',
        'creditos_requieren_autorizacion' => 'boolean'
    ];

    public function moneda()
    {
        return $this->belongsTo(Moneda::class, 'id_moneda', 'id_moneda');
    }


    public function rutas()
    {
        return $this->hasMany(Ruta::class, 'id_oficina');
    }

}
