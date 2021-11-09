<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRabItemHeadersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rab_item_headers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rab_id');
            $table->string('name')->nullable();
            $table->timestamps();
            $table->foreign('rab_id')->references('id')->on('rabs')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rab_item_headers');
    }
}
