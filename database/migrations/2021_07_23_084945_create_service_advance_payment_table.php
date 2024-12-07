<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceAdvancePaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_advance_payment', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('address_id')->unsigned()->index()->nullable();
            $table->foreign('address_id')->references('id')->on('units_address')->onDelete('cascade');
            $table->bigInteger('customer_id')->unsigned()->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->enum('service_type',['IN', 'CT', 'IP', 'HR'])->nullable();// IN=>Internet, CT=>Cable TV, IP=>IP TV, HR=>House Rent
            $table->date('start_date')->format('Y-m-d')->nullable();
            $table->date('end_date')->format('Y-m-d')->nullable();
            $table->bigInteger('period')->nullable();
            $table->double('monthly_fee',8,2)->nullable();
            $table->double('total_amount',8,2)->nullable();
            $table->enum('payment_mode',['CA', 'BA', 'CH', 'OT'])->default('CA');
            $table->string('payment_description')->nullable();
            $table->enum('payment_by',['CP', 'CR'])->default('CP');
            $table->enum('paid',['Y', 'N'])->default('N');
            $table->date('paid_date')->format('Y-m-d')->nullable();
            $table->bigInteger('user_id')->unsigned()->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');  
            $table->text('remark')->nullable();
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
        Schema::dropIfExists('service_advance_payment');
    }
}
