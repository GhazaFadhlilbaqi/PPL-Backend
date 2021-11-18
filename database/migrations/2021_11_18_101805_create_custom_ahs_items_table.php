<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomAhsItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_ahs_items', function (Blueprint $table) {
            $table->id();
            $table->string('custom_ahs_id');
            $table->string('name')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->float('coefficient')->default(0.0);
            $table->enum('section', ['labor', 'ingredients', 'tools', 'others']);
            $table->string('custom_ahs_itemable_id');
            $table->string('custom_ahs_itemable_type');
            $table->foreign('custom_ahs_id')->references('id')->on('custom_ahs')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onUpdate('cascade')->onDelete('restrict');
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
        Schema::dropIfExists('custom_ahs_items');
    }
}
