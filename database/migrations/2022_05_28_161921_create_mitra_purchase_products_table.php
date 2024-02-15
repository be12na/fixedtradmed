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
        Schema::create('mitra_purchase_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mitra_purchase_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('branch_product_id')->default(0);
            $table->unsignedBigInteger('branch_stock_id')->default(0);
            $table->unsignedSmallInteger('product_unit');
            $table->unsignedBigInteger('product_zone');
            $table->unsignedBigInteger('product_price');
            // v2
            $table->unsignedBigInteger('product_zone_id')->nullable();
            $table->boolean('is_promo')->default(false);
            $table->unsignedBigInteger('product_zone_price')->default(0);
            // end v2
            $table->unsignedInteger('product_qty');
            $table->unsignedBigInteger('total_price');
            $table->unsignedDecimal('persen_mitra', 4, 2);
            $table->unsignedBigInteger('profit_mitra')->default(0);
            $table->unsignedDecimal('persen_foundation', 4, 2);
            $table->unsignedBigInteger('foundation')->default(0);
            // v2
            $table->unsignedBigInteger('discount_id')->nullable();
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->boolean('coupon_is_percent')->default(false);
            $table->unsignedDecimal('coupon_percent', 8, 4)->default(0);
            $table->unsignedBigInteger('coupon_discount')->default(0);
            // end v2
            $table->unsignedBigInteger('total_profit')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('note', 250)->nullable();
            $table->boolean('is_v2')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mitra_purchase_products');
    }
};
