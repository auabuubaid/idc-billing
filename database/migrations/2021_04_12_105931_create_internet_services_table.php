<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInternetServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('internet_services', function (Blueprint $table) {
            $table->id();
            $table->string('service_name')->nullable();
            $table->string('speed')->nullable();
            $table->enum('speed_unit',['Bps', 'Kbps', 'Mbps', 'Gbps', 'Tbps'])->default('Bps');
            $table->string('upload_speed')->nullable();
            $table->enum('upload_speed_unit',['Bps', 'Kbps', 'Mbps', 'Gbps', 'Tbps'])->default('Bps');
            $table->double('deposit_fee',8,2)->nullable();
            $table->double('monthly_fee',8,2)->nullable();
            $table->double('installation_fee',8,2)->nullable();
            $table->float('vat',8,2)->nullable();
            $table->enum('data_usage',['U', 'L'])->default('U');
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
        Schema::dropIfExists('internet_services');
    }
}
