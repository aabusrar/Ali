<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('day');
            $table->string('time_from');
            $table->string('time_to');
            $table->string('course_name');
            $table->string('course_code')->nullable();
            $table->string('room')->nullable();
            $table->string('instructor')->nullable();
            $table->string('section')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('students')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('schedules');
    }
};