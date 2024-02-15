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
        Schema::create('branch_sales_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_sale_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('branch_product_id');
            $table->unsignedBigInteger('branch_stock_id');
            $table->unsignedSmallInteger('product_unit');
            $table->unsignedBigInteger('product_zone');
            $table->unsignedBigInteger('product_price');
            $table->unsignedInteger('product_qty');
            $table->unsignedBigInteger('total_price');
            $table->unsignedDecimal('persen_crew', 4, 2);
            $table->unsignedBigInteger('profit_crew')->default(0);
            $table->unsignedDecimal('persen_foundation', 4, 2);
            $table->unsignedBigInteger('foundation')->default(0);
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
        Schema::dropIfExists('branch_sales_products');
    }
};
