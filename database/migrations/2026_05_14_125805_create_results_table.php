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
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('person_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('answer_id');

            $table->foreign('person_id')->references('id')->on('persons')->nullable()->default(null);
            $table->foreign('question_id')->references('id')->on('questions')->nullable()->default(null);
            $table->foreign('answer_id')->references('id')->on('answers')->nullable()->default(null);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
