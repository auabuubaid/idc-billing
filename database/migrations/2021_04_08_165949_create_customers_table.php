<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('address_id')->unsigned()->index()->nullable();
            $table->foreign('address_id')->references('id')->on('units_address')->onDelete('cascade');
            $table->enum('type',['P', 'S'])->default('P');
            $table->enum('is_living',['Y', 'N'])->default('N');
            $table->string('name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('mobile')->nullable();
            $table->enum('sex',['M', 'F', 'O'])->default('M');
            $table->string('shop_name')->nullable();
            $table->string('shop_email')->unique()->nullable();
            $table->string('shop_mobile')->nullable();
            $table->string('vat_no')->nullable();
            $table->string('country')->nullable();
            $table->bigInteger('created_by')->unsigned()->index()->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('updated_by')->unsigned()->index()->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('customers');
    }
}
