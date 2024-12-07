<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInternetHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('internet_history', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('address_id')->unsigned()->index()->nullable();
            $table->foreign('address_id')->references('id')->on('units_address')->onDelete('cascade');
            $table->bigInteger('customer_id')->unsigned()->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->bigInteger('plan_id')->unsigned()->index()->nullable();
            $table->foreign('plan_id')->references('id')->on('internet_services')->onDelete('cascade');
            $table->enum('entry_type',['NR', 'CP', 'CL', 'TS', 'SS', 'RC','MP'])->nullable();
            $table->enum('entry_status',['PAY', 'CON', 'INS', 'CNF'])->nullable();
            $table->enum('plan_level',['UPGRADE', 'DOWNGRADE','NORMAL'])->default('NORMAL');
            $table->date('registration_date')->format('Y-m-d')->nullable();
            $table->timestamp('start_date_time')->nullable();
            $table->timestamp('end_date_time')->nullable();
            $table->date('monthly_invoice_date')->format('Y-m-d')->nullable();
            $table->date('suspension_start_date')->format('Y-m-d')->nullable();
            $table->date('suspension_end_date')->format('Y-m-d')->nullable();
            $table->bigInteger('suspension_period')->nullable();
            $table->date('terminate_date')->format('Y-m-d')->nullable();
            $table->string('customer_mobile')->nullable();
            $table->enum('agreement_period',['N', '1', '2', '3', '4', '5'])->default('N');
            $table->text('other_condition')->nullable();
            $table->text('plan_remark')->nullable();
            $table->double('deposit_fee',8,2)->nullable();
            $table->double('previous_deposit_fee',8,2)->nullable();
            $table->double('monthly_fee',8,2)->nullable();
            $table->double('installation_fee',8,2)->nullable();
            $table->double('reinstallation_fee',8,2)->nullable();
            $table->double('reconnect_fee',8,2)->nullable();
            $table->double('others_fee',8,2)->nullable();
            $table->double('balance',8,2)->nullable();
            $table->double('vat_amount',8,2)->nullable();
            $table->double('refund_amount',8,2)->nullable();
            $table->double('due_amount',8,2)->nullable();
            $table->double('total_amount',8,2)->nullable();
            $table->double('discount',8,2)->nullable();
            $table->enum('payment_mode',['CA', 'BA', 'CH', 'OT'])->default('CA');
            $table->string('payment_description')->nullable();
            $table->enum('payment_by',['CP', 'CR'])->default('CP');
            $table->enum('paid',['Y', 'N'])->default('N');
            $table->date('paid_date')->format('Y-m-d')->nullable();
            $table->bigInteger('refrence_id')->unsigned()->index()->nullable();
            $table->foreign('refrence_id')->references('id')->on('internet_history')->onDelete('cascade');
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
        Schema::dropIfExists('internet_history');
    }
}
