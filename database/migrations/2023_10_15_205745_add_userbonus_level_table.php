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
        Schema::table('user_bonuses', function (Blueprint $table) {
            $table->unsignedInteger('level')->default(0)->after('bonus_type');
            $table->unsignedBigInteger('purchase_product_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_bonuses', function (Blueprint $table) {
            $table->dropColumn(['level', 'purchase_product_id', 'product_id']);
        });
    }
};
