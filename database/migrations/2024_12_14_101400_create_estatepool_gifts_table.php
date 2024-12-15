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
        Schema::create('estatepool_gifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pool');
            $table->string('name', 255);
            $table->string('date_close', 30)->nullable();
            $table->unsignedBigInteger('id_winner')->nullable();
            $table->unsignedBigInteger('id_not_winner')->nullable();
            $table->integer('priority')->default(0);
            $table->tinyInteger('general')->default(0);

            $table->foreign('id_pool')->references('id')->on('estatepool')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estatepool_gifts');
    }
};
