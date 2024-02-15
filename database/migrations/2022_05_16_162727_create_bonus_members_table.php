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
        Schema::create('bonus_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedSmallInteger('position_id');
            $table->unsignedSmallInteger('bonus_type');
            $table->date('bonus_date');
            $table->boolean('is_internal')->default(true);
            $table->unsignedSmallInteger('level_id')->nullable();
            $table->unsignedBigInteger('bonus_base');
            $table->unsignedDecimal('bonus_percent', 6, 4);
            $table->unsignedDecimal('bonus_amount', 16, 4);
            $table->unsignedBigInteger('setting_id');
            $table->unsignedBigInteger('transfer_id');
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->unsignedInteger('qty_box')->default(0);
            $table->unsignedInteger('qty_pcs')->default(0);
            $table->json('details')->nullable();
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
        Schema::dropIfExists('bonus_members');
    }
};
