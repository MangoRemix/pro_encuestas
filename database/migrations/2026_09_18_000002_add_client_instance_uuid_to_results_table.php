<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Identificador de instancia de encuesta generado por el cliente (app
     * móvil offline). Permite que reintentar una subida cortada por falta de
     * conexión no duplique resultados: si ya existe algún Result con este
     * uuid, la subida se trata como ya procesada.
     */
    public function up(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->string('client_instance_uuid')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->dropColumn('client_instance_uuid');
        });
    }
};
