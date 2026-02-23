<?php

namespace Database\Seeders;

use App\Models\Oficina;
use App\Models\Ruta;
use App\Models\User;
use Illuminate\Database\Seeder;

class OficinaRutasSeeder extends Seeder
{
    public function run()
    {
        $oficina = Oficina::firstOrCreate(
            ['nombre' => 'Oficina Principal'],
            [
                'id_moneda' => 1,
                'pais' => 'Perú',
                'codigo' => 'OF-001',
                'max_abonos_diarios' => 1,
                'porcentajes_credito' => '20,24,30',
                'activar_seguros' => false,
            ]
        );

        $ruta1 = Ruta::firstOrCreate(
            [
                'nombre' => 'Ruta 1',
                'id_oficina' => $oficina->id_oficina,
            ],
            [
                'creada_en' => now()->toDateString(),
                'activa' => true,
                'id_tipo_documento' => null,
                'id_tipo_cobro' => null,
                'agregar_ceros_cantidades' => false,
                'porcentajes_credito' => null,
                'id_usuario' => null,
            ]
        );

        $ruta2 = Ruta::firstOrCreate(
            [
                'nombre' => 'Ruta 2',
                'id_oficina' => $oficina->id_oficina,
            ],
            [
                'creada_en' => now()->toDateString(),
                'activa' => true,
                'id_tipo_documento' => null,
                'id_tipo_cobro' => null,
                'agregar_ceros_cantidades' => false,
                'porcentajes_credito' => null,
                'id_usuario' => null,
            ]
        );

        $usuarios = User::whereIn('email', [
            'john.doe@helper.app',
            'fiorela@helper.app',
        ])->get()->keyBy('email');

        if ($usuarios->isNotEmpty()) {
            $rutaIds = [$ruta1->id_ruta, $ruta2->id_ruta];

            foreach ($rutaIds as $rutaId) {
                $ruta = Ruta::find($rutaId);

                if (! $ruta) {
                    continue;
                }

                $pivotData = [];

                if (isset($usuarios['john.doe@helper.app'])) {
                    $pivotData[$usuarios['john.doe@helper.app']->id] = ['es_principal' => true];
                }

                if (isset($usuarios['fiorela@helper.app'])) {
                    $pivotData[$usuarios['fiorela@helper.app']->id] = ['es_principal' => false];
                }

                if (! empty($pivotData)) {
                    $ruta->usuarios()->syncWithoutDetaching($pivotData);
                }
            }
        }
    }
}
