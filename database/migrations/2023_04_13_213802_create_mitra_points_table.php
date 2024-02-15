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
        Schema::create('mitra_points', function (Blueprint $table) {
            $table->id();
            $table->date('point_date');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('from_user_id')->nullable();
            $table->unsignedSmallInteger('point_type');
            $table->unsignedBigInteger('purchase_id');
            $table->unsignedBigInteger('purchase_product_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('product_qty');
            $table->unsignedInteger('point_unit');
            $table->unsignedInteger('point');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mitra_points');
    }
};
