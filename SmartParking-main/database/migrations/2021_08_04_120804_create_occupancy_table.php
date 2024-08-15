<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOccupancyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('occupancy', function (Blueprint $table) {
            $table->integer('carpark_id')->unsigned();
            $table->integer('spaces_available');
            $table->integer('timestamp');
        });

        Schema::table('occupancy', function (Blueprint $table) {
            $table->primary(['carpark_id', 'timestamp']);
            $table->foreign('carpark_id')->references('id')->on('places');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('occupancy');
    }
}
