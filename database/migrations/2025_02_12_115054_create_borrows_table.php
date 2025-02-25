<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('borrows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Borrower (Foreign Key)
            $table->unsignedBigInteger('equipment_id'); // Equipment (Foreign Key)
            $table->enum('condition', ['1', '2', '3'])->default('1');
            $table->enum('status', ['1', '2', '3', '4', '5'])->default('1');
            $table->date('date_borrowed');
            $table->date('date_returned')->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('equipment_id')->references('id')->on('equipment')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('borrows');
    }
};

