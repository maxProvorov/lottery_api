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
        Schema::create('users_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user')->nullable();
            $table->unsignedBigInteger('id_balance')->nullable();
            $table->decimal('sum', 20, 10)->default(0);
            $table->decimal('stat_sum', 20, 10)->default(0);
            $table->integer('status')->default(1);
            $table->integer('show_balance')->default(1);

            $table->foreign('id_balance')->references('id')->on('balances')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_balances');
    }
};
