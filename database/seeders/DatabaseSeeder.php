<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $this->call(DefaultUserSeeder::class);
        $this->call(PermissionsSeeder::class);

        $this->call(TipoDocumentoSeeder::class);
        $this->call(TipoCobroSeeder::class);
        $this->call(MonedaSeeder::class);

        $this->call(TipoPagoSeeder::class);
        $this->call(ConceptosSeeder::class);

        $this->call(EstadosDepartamentoSeeder::class);
        $this->call(OficinaRutasSeeder::class);





    }
}
