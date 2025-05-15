<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_prices', function (Blueprint $table) {
            $table->id();
            $table->string('subscription_id');
            $table->enum('duration_type', ['YEARLY', 'MONTHLY'])->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('discounted_price', 10, 2)->default(0);
            $table->integer('min_duration')->default(1);
            $table->boolean('is_active');
            $table->timestamps();

            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscription_prices');
    }
}
