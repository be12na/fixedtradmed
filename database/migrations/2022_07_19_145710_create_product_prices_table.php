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
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('zone_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('normal_price')->default(0);
            $table->unsignedBigInteger('normal_retail_price')->default(0);
            $table->unsignedBigInteger('mitra_price')->default(0);
            $table->unsignedBigInteger('mitra_promo_price')->default(0);
            $table->unsignedBigInteger('mitra_basic_bonus')->default(0);
            $table->unsignedBigInteger('mitra_premium_bonus')->default(0);
            $table->unsignedBigInteger('distributor_bonus')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
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
        Schema::dropIfExists('product_prices');
    }
};
