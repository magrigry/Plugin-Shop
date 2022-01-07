<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscountBasedOnSpentAmount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shop_discounts', function (Blueprint $table) {
            $table->integer('min_total_spent')->nullable(true);
            $table->integer('max_total_spent')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shop_discounts', function (Blueprint $table) {
            $table->dropColumn('min_total_spent');
            $table->dropColumn('max_total_spent');
        });
    }
}
