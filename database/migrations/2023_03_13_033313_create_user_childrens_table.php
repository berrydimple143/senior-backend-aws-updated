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
        Schema::create('user_children', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 191)->nullable()->default(null);
            $table->string('occupation', 191)->nullable()->default(null);
            $table->string('income', 100)->nullable()->default(null);
            $table->string('age', 10)->nullable()->default(null);
            $table->string('dependency', 254)->nullable()->default(null);
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
        Schema::dropIfExists('user_childrens');
    }
};
