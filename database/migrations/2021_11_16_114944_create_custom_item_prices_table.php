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
            $table->string('code')->nullable(); // User defined code / id
            $table->unsignedBigInteger('custom_item_price_group_id');
            $table->unsignedBigInteger('unit_id');
            $table->unsignedBigInteger('project_id');
            $table->string('name')->nullable();
            $table->boolean('is_default')->default(false);
            $table->double('price')->default(0);
            $table->double('default_price')->nullable()->default(null);
            $table->timestamps();
            $table->foreign('custom_item_price_group_id')->references('id')->on('custom_item_price_groups')->onDelete('cascade')->onUpdate('cascade');
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
