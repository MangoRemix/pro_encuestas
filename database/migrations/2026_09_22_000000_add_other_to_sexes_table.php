<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * El catálogo de sexo/género solo tenía M/F; se agrega "Otro" para el
     * formulario de datos del encuestado (web y móvil). Idempotente por
     * abbreviation, igual que SexSeeder, para que sea seguro re-ejecutar.
     */
    public function up(): void
    {
        DB::table('sexes')->updateOrInsert(
            ['abbreviation' => 'O'],
            ['abbreviation' => 'O', 'description' => 'OTHER']
        );
    }

    public function down(): void
    {
        DB::table('sexes')->where('abbreviation', 'O')->delete();
    }
};
