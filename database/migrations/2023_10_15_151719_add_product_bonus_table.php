<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('bonus_sponsor')->default(0);
            $table->unsignedBigInteger('bonus_sponsor_ro')->default(0);
            $table->unsignedBigInteger('bonus_cashback_ro')->default(0);
            $table->unsignedSmallInteger('package_range')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['bonus_sponsor', 'bonus_sponsor_ro', 'bonus_cashback_ro', 'package_range']);
        });
    }
};
