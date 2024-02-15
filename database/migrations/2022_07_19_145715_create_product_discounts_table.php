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
        Schema::create('product_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('mitra_type');
            $table->unsignedSmallInteger('zone_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedSmallInteger('discount_category');
            $table->unsignedBigInteger('min_qty');
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('set_by')->nullable();
            $table->unsignedBigInteger('previous_id')->nullable();
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
        Schema::dropIfExists('product_discounts');
    }
};
