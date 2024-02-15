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
        Schema::table('mitra_points', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_id')->default(0)->change();
            $table->unsignedBigInteger('purchase_product_id')->default(0)->change();
            $table->unsignedBigInteger('product_id')->default(0)->change();
            $table->unsignedBigInteger('product_qty')->default(0)->change();
            $table->unsignedBigInteger('point_unit')->default(0)->change();
            $table->unsignedBigInteger('user_package_id')->default(0)->after('point_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mitra_points', function (Blueprint $table) {
            $table->dropColumn(['user_package_id']);
        });
    }
};
