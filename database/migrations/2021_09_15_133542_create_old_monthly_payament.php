<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOldMonthlyPayament extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('old_monthly_payament', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('customer_no')->unsigned()->nullable();
            $table->string('customer_name')->nullable();
            $table->string('address')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('invoice_date')->nullable();
            $table->string('due_date')->nullable();
            $table->string('plan_name')->nullable();
            $table->string('speed')->nullable();            
            $table->double('monthly_fee',8,2)->nullable();
            $table->double('balance',8,2)->nullable();
            $table->double('vat_amount',8,2)->nullable();
            $table->double('others_fee',8,2)->nullable();
            $table->double('discount',8,2)->nullable();
            $table->double('total_amount',8,2)->nullable();            
            $table->string('paid_date')->nullable();
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
        Schema::dropIfExists('old_monthly_payament');
    }
}
