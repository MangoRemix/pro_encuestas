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
            $table->string('name', 200)->nullable()->change();
            $table->unsignedBigInteger('rol_id')->nullable()->change();
            $table->unsignedBigInteger('parish_id')->nullable()->change();
            $table->unsignedBigInteger('sex_id')->nullable()->change();
            $table->unsignedBigInteger('age_range_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->string('name', 200)->nullable(false)->change();
            $table->unsignedBigInteger('rol_id')->nullable(false)->change();
            $table->unsignedBigInteger('parish_id')->nullable(false)->change();
            $table->unsignedBigInteger('sex_id')->nullable(false)->change();
            $table->unsignedBigInteger('age_range_id')->nullable(false)->change();
        });
    }
};

