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
        Schema::create('estatepool_usertickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket', 9);
            $table->unsignedBigInteger('id_ticket')->nullable();
            $table->unsignedBigInteger('id_user')->nullable();
            $table->unsignedBigInteger('id_pool');
            $table->unsignedBigInteger('id_gift')->nullable();
            $table->tinyInteger('win')->default(0);

            $table->foreign('id_pool')->references('id')->on('estatepool')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estatepool_usertickets');
    }
};
