<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCabletvServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cabletv_services', function (Blueprint $table) {
            $table->id();
            $table->string('plan_name')->nullable();
            $table->double('installation_fee',8,2)->nullable();
            $table->double('monthly_fee',8,2)->nullable();
            $table->double('per_tv_fee',8,2)->nullable();
            $table->enum('status',['A', 'N'])->default('N');
            $table->bigInteger('created_by')->unsigned()->index()->nullable();
            $table->foreign('created_by')->references('id')->on('admins')->onDelete('cascade');
            $table->bigInteger('updated_by')->unsigned()->index()->nullable();
            $table->foreign('updated_by')->references('id')->on('admins')->onDelete('cascade');
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
        Schema::dropIfExists('cabletv_services');
    }
}
