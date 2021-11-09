<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRabItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rab_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rab_id');
            $table->unsignedBigInteger('rab_item_header_id')->nullable();
            $table->string('name')->nullable();
            $table->string('ahs_id')->nullable();
            $table->double('volume')->default(0.0);
            $table->unsignedBigInteger('unit_id');
            $table->timestamps();
            $table->foreign('rab_item_header_id')->references('id')->on('rab_item_headers')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('rab_id')->references('id')->on('rabs')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('ahs_id')->references('id')->on('ahs')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rab_items');
    }
}
