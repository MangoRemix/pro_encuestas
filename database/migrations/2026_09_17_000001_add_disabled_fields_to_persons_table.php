<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            // Sin after(): Postgres no lo soporta, y Laravel lo ignoraría en
            // silencio en este driver.
            $table->timestamp('disabled_at')->nullable();
            $table->text('disabled_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn(['disabled_at', 'disabled_reason']);
        });
    }
};
