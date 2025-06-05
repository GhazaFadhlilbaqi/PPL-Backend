<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInstitutionNameAndDepartmentToProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('institution_name')->after('subscription_id')->nullable();
            $table->string('department_name')->after('institution_name')->nullable();
            $table->string('activity')->nullable()->change();
            $table->string('job')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['institution_name', 'department']);
            $table->string('activity')->nullable(false)->change();
            $table->string('job')->nullable(false)->change();
        });
    }
}
