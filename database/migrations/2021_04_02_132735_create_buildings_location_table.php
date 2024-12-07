<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuildingsLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buildings_location', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('location',['R1','R2']);
            $table->enum('type',['A','T','S','V']);
            $table->enum('status',['A', 'N'])->default('N');
            $table->integer('sort_order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('buildings_location');
    }
}
