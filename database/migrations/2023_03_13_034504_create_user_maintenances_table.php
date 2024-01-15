<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('medicine', 254)->nullable()->default(null);
            $table->string('dosage', 254)->nullable()->default(null);
            $table->string('quantity', 254)->nullable()->default(null);
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_maintenances');
    }
};
