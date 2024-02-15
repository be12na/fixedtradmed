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
        Schema::create('branch_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_product_id');
            $table->date('date_from');
            $table->date('date_to');
            $table->unsignedSmallInteger('stock_type')->default(0)->comment('0=manager input tanpa selisih, 1=created by admin, 2=edited by admin, 3=penambahan jumlah di minggu berjalan oleh admin');
            $table->string('stock_info', 250)->nullable();
            $table->unsignedInteger('last_stock')->default(0);
            $table->unsignedInteger('output_stock')->default(0);
            $table->integer('rest_stock')->default(0);
            $table->unsignedInteger('input_manager')->default(0);
            $table->integer('diff_stock')->default(0);
            $table->integer('input_admin')->default(0);
            $table->integer('total_stock')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
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
        Schema::dropIfExists('branch_stocks');
    }
};
