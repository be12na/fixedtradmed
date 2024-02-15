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
            $table->unsignedBigInteger('bonus_cashback')->default(0)->after('bonus_sponsor_ro');
            $table->unsignedSmallInteger('bonus_cashback_condition')->default(0)->after('bonus_cashback');
            $table->unsignedSmallInteger('bonus_cashback_ro_condition')->default(0)->after('bonus_cashback_ro');
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
            $table->dropColumn(['bonus_cashback', 'bonus_cashback_condition', 'bonus_cashback_ro_condition']);
        });
    }
};
