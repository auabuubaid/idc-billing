<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIpAddressPasswordIdCardImageToCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->ipAddress('ip_address')->nullable()->after('country');
            $table->string('internet_password')->nullable()->after('internet_id');
            $table->string('id_card_image')->nullable()->after('mobile');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('ip_address');
            $table->dropColumn('internet_password');
            $table->dropColumn('id_card_image');
        });
    }
}
