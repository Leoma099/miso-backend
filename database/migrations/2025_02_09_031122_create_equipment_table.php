<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('equipment_type'); // Monitor, Mouse, Printer, etc.
            $table->string('brand'); // Dell, HP, Samsung, etc.
            $table->string('model');
            $table->enum('condition', ['1', '2', '3']);
            $table->enum('availability', ['1', '2']);
            $table->enum('status', ['1', '2']);
            $table->date('registered_date');
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
        Schema::dropIfExists('equipment');
    }
}
