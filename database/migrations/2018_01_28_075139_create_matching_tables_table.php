<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchingTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('matchingtable', function (Blueprint $table) {
            $table->increments('id');
            $table->text('timestamp');
            $table->text('difference');
            $table->text('matched_map_id');
            $table->text('distance');
            $table->text('matched_longitude');
            $table->text('matched_heading');
            $table->text('mo_id');
            $table->text('matched_latitude');
            $table->text('link_id');
            $table->text('speed')->nullable();
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
        Schema::dropIfExists('matchingtable');
    }
}
