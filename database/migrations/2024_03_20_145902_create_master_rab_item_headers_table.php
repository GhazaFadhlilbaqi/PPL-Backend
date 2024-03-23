<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterRabItemHeadersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_rab_item_headers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('master_rab_id');
            $table->string('name')->nullable();
            $table->foreign('master_rab_id')->references('id')->on('master_rabs')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('master_rab_item_headers');
    }
}
