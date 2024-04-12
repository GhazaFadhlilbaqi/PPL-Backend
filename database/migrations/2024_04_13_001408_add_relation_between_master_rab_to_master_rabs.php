<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationBetweenMasterRabToMasterRabs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('master_rabs', function (Blueprint $table) {
            $table->unsignedBigInteger('master_rab_category_id')->nullable()->after('name');
            $table->foreign('master_rab_category_id')->references('id')->on('master_rab_categories')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('master_rabs', function (Blueprint $table) {
            //
        });
    }
}
