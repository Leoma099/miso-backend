<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('borrows', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('account_id')->nullable();
            $table->bigInteger('equipment_id')->nullable();
            $table->string('full_name');
            $table->string('id_number');
            $table->string('office_name');
            $table->string('office_address');
            $table->string('type');
            $table->string('brand');
            $table->string('model');
            $table->integer('quantity')->default(0);
            $table->string('property_number');
            $table->string('position');
            $table->string('mobile_number');
            $table->tinyInteger('status')->default(1);
            $table->longText('purpose')->nullable();
            $table->date('date_borrow');
            $table->date('date_return')->nullable();
            $table->timestamps();

            $table->string('agent')->nullable();
            $table->date('date')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('borrows');
    }
};

