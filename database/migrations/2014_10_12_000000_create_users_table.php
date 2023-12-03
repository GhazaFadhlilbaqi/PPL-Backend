<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 32);
            $table->string('last_name', 32)->nullable();
            $table->string('email', 255)->unique();
            $table->string('password');
            $table->string('phone', 32)->unique();
            $table->string('address', 255)->nullable();
            $table->string('job', 64);
            $table->string('photo', 32)->nullable()->default(null);
            $table->text('verification_token')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->integer('token_amount')->default(0);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
