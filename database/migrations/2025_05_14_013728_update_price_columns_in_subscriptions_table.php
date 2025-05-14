<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePriceColumnsInSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->renameColumn('price', 'monthly_price');
            $table->decimal('yearly_price', 10, 2)->after('price')->nullable();
            $table->integer('min_month')->after('yearly_price')->default(1);
            $table->dropColumn('subscription_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->renameColumn('monthly_price', 'price');
            $table->dropColumn('yearly_price');
            $table->dropColumn('min_month');
            $table->enum('subscription_type', ['DAILY', 'THREEDAYS', 'MONTHLY', 'ANNUALLY', 'QUARTERLY']);
        });
    }
}
