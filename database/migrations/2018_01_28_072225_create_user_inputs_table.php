<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserInputsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('USERINPUT', function (Blueprint $table) {
            $table->increments('id');
            $table->double('latitude','20','10');
            $table->text('heading');
            $table->text('timestamp');
            $table->text('speed');
            $table->double('longitude','20','10');
            $table->text('mo_id');
            $table->text('driver_id');
            $table->text('trip_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('USERINPUT');
    }
}
