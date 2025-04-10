<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentReleaseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment_release', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('borrow_id');

            $table->string('type');
            $table->string('released_to');
            $table->string('department');
            $table->date('date');
            $table->string('full_name');

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
        Schema::dropIfExists('equipment_release');
    }
}
