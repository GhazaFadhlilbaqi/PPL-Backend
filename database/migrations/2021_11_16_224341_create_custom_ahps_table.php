<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomAhpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_ahps', function (Blueprint $table) {
            $table->id();
            $table->string('code', 16);
            $table->unsignedBigInteger('project_id');
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
            $table->double('pbb')->default(0);
            $table->double('ppl')->default(0);
            $table->double('pbk')->default(0);
            $table->double('ppp')->default(0);
            $table->double('m')->default(0);
            $table->double('n')->default(0);
            $table->timestamps();
            $table->foreign('project_id')->references('id')->on('projects')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_ahps');
    }
}
