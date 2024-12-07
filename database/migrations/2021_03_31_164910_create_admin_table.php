<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('admin_type',['S', 'A'])->default('A');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->enum('read',['Y', 'N'])->default('Y');
            $table->enum('write',['Y', 'N'])->default('Y');
            $table->enum('delete',['Y', 'N'])->default('N');
            $table->enum('download',['Y', 'N'])->default('Y');
            $table->enum('upload',['Y', 'N'])->default('Y');
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
        Schema::dropIfExists('admin');
    }
}
