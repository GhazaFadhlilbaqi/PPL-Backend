<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomItemPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_item_prices', function (Blueprint $table) {
            $table->id();
            $table->string('code'); // User defined code / id
            $table->string('custom_item_priceable_id')->nullable();
            $table->string('custom_item_priceable_type')->nullable();
            $table->unsignedBigInteger('unit_id');
            $table->unsignedBigInteger('project_id');
            $table->string('name');
            $table->unsignedInteger('price');
            $table->timestamps();
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_item_prices');
    }
}
