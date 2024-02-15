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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->unsignedInteger('product_category_id');
            $table->string('name', 200);
            $table->unsignedSmallInteger('satuan');
            $table->unsignedInteger('isi')->default(1);
            $table->unsignedSmallInteger('satuan_isi')->nullable();
            $table->text('notes')->nullable();
            $table->longText('description')->nullable();
            $table->unsignedDecimal('harga_a', 16, 2)->default(0);
            $table->unsignedDecimal('harga_b', 16, 2)->default(0);
            $table->unsignedDecimal('harga_c', 16, 2)->default(0);
            $table->unsignedDecimal('harga_d', 16, 2)->default(0);

            $table->unsignedBigInteger('eceran_a')->default(0);
            $table->unsignedBigInteger('eceran_b')->default(0);
            $table->unsignedBigInteger('eceran_c')->default(0);
            $table->unsignedBigInteger('eceran_d')->default(0);

            $table->unsignedInteger('self_point')->default(1);
            $table->unsignedInteger('upline_point')->default(1);

            $table->boolean('is_publish')->default(false);

            $table->string('image', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('active_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['product_category_id', 'name', 'satuan'], 'uq_product_unik');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
