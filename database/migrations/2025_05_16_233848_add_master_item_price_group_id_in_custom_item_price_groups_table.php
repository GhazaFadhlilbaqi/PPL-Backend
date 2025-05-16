<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMasterItemPriceGroupIdInCustomItemPriceGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('custom_item_price_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('master_item_price_group_id')
                ->after('id')
                ->nullable();
            $table->foreign('master_item_price_group_id')
                ->references('id')
                ->on('item_price_groups')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('custom_item_price_groups', function (Blueprint $table) {
            $table->dropColumn('master_item_price_group_id');
        });
    }
}
