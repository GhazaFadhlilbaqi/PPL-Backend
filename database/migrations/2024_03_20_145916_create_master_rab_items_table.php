<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterRabItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_rab_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('master_rab_id');
            $table->unsignedBigInteger('master_rab_item_header_id')->nullable();
            $table->string('name')->nullable();
            $table->string('ahs_id')->nullable();
            $table->double('volume')->default(0.0);
            $table->double('price')->nullable()->default(null);
            $table->unsignedBigInteger('unit_id');
            $table->foreign('master_rab_item_header_id')->references('id')->on('master_rab_item_headers')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('master_rab_id')->references('id')->on('master_rabs')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('ahs_id')->references('id')->on('ahs')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('restrict')->onUpdate('cascade');
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
        Schema::dropIfExists('master_rab_items');
    }
}
