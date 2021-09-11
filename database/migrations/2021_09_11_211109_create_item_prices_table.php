<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_prices', function (Blueprint $table) {
            $table->string('id', 16)->primary();
            $table->unsignedBigInteger('item_price_group_id');
            $table->unsignedBigInteger('unit_id');
            $table->string('name', 128);
            $table->unsignedInteger('price')->default(0);
            $table->timestamps();
            $table->foreign('item_price_group_id')->references('id')->on('item_price_groups')->onDelete('restrict')->onUpdate('cascade');
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
        Schema::dropIfExists('admin_master_item_prices');
    }
}
