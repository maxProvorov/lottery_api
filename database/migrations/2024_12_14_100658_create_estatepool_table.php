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
        Schema::create('estatepool', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date_start');
            $table->dateTime('date_close');
            $table->decimal('sum', 10, 6)->default(0);
            $table->decimal('sum_goal', 10, 6)->default(0);
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estatepool');
    }
};
