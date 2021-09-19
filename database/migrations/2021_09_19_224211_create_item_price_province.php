<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemPriceProvince extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_price_province', function (Blueprint $table) {
            $table->id();
            $table->string('item_price_id');
            $table->unsignedTinyInteger('province_id');
            $table->unsignedInteger('price')->default(0);
            $table->timestamps();
            $table->foreign('item_price_id')->references('id')->on('item_prices')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('province_id')->references('id')->on('provinces')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_price_province');
    }
}
