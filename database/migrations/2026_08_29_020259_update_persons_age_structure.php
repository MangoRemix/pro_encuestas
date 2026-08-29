<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->integer('age')->nullable()->after('sex_id');
            $table->dropForeign(['age_range_id']);
            $table->dropColumn('age_range_id');
        });

        Schema::dropIfExists('age_ranges');
    }

    public function down(): void
    {
        Schema::create('age_ranges', function (Blueprint $table) {
            $table->id();
            $table->integer('init_range');
            $table->integer('finish_range');
            $table->string('range');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('persons', function (Blueprint $table) {
            $table->foreignId('age_range_id')->nullable()->constrained('age_ranges');
            $table->dropColumn('age');
        });
    }
};
