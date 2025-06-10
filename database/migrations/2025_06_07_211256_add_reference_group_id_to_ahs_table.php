<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferenceGroupIdToAhsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ahs', function (Blueprint $table) {
            $table->unsignedBigInteger('reference_group_id')->nullable()->after('name');

            $table->foreign('reference_group_id')->references('id')->on('ahs_reference_groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ahs', function (Blueprint $table) {
            $table->dropForeign(['reference_group_id']);
            $table->dropColumn('reference_group_id');
        });
    }
}
