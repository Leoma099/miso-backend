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
            $table->bigInteger('equipment_id');
            $table->string('full_name');
            $table->string('office_name');
            $table->string('office_address');
            $table->string('type');
            $table->string('position');
            $table->string('mobile_number');
            $table->tinyInteger('status');
            $table->longText('purpose');
            $table->date('date_borrow');
            $table->date('date_return')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('borrows');
    }
};

