<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAhsItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ahs_items', function (Blueprint $table) {
            $table->id();
            $table->string('ahs_id');
            $table->string('name')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->float('coefficient')->default(0.0);
            $table->enum('section', ['labor', 'ingredients', 'tools', 'others']);
            $table->string('ahs_itemable_id');
            $table->string('ahs_itemable_type');
            $table->foreign('ahs_id')->references('id')->on('ahs')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('ahs_items');
    }
}
