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
        Schema::create('user_families', function (Blueprint $table) {
            $table->id();
            $table->string('spouse_first_name', 191)->nullable()->default(null);
            $table->string('spouse_middle_name', 191)->nullable()->default(null);
            $table->string('spouse_last_name', 191)->nullable()->default(null);
            $table->string('spouse_extension_name', 191)->nullable()->default(null);
            $table->string('father_first_name', 191)->nullable()->default(null);
            $table->string('father_middle_name', 191)->nullable()->default(null);
            $table->string('father_last_name', 191)->nullable()->default(null);
            $table->string('father_extension_name', 191)->nullable()->default(null);
            $table->string('mother_first_name', 191)->nullable()->default(null);
            $table->string('mother_middle_name', 191)->nullable()->default(null);
            $table->string('mother_last_name', 191)->nullable()->default(null);
            $table->string('mother_extension_name', 191)->nullable()->default(null);
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
        Schema::dropIfExists('user_families');
    }
};
