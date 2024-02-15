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
        Schema::create('setting_mitra_cashbacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('mitra_type');
            $table->unsignedBigInteger('min_purchase');
            $table->unsignedDecimal('percent', 4, 2);
            $table->unsignedBigInteger('set_by')->nullable();
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
        Schema::dropIfExists('setting_mitra_cashbacks');
    }
};
