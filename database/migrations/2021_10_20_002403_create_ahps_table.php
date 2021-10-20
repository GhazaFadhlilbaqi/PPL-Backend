<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAhpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ahps', function (Blueprint $table) {
            $table->string('id', 16)->unique()->primary();
            $table->string('name', 64);
            $table->double('Pw')->default(0);
            $table->double('Cp')->default(0);
            $table->double('A')->default(0);
            $table->double('W')->default(0);
            $table->double('B')->default(0);
            $table->double('i')->default(0);
            $table->double('U1')->default(0);
            $table->double('U2')->default(0);
            $table->double('Mb')->default(0);
            $table->double('Ms')->default(0);
            $table->double('Mp')->default(0);
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
        Schema::dropIfExists('ahps');
    }
}
