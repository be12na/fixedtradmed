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
        Schema::create('branch_payment_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_payment_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('branch_product_id');
            $table->unsignedBigInteger('branch_stock_id');
            $table->unsignedSmallInteger('product_unit');
            $table->unsignedBigInteger('product_zone');
            $table->unsignedBigInteger('product_price');
            $table->unsignedInteger('product_qty');
            $table->unsignedBigInteger('total_price');
            $table->unsignedBigInteger('discount_id')->default(0);
            $table->unsignedBigInteger('product_discount')->default(0);
            $table->unsignedBigInteger('total_discount')->default(0);
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
        Schema::dropIfExists('branch_payment_details');
    }
};
