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
        Schema::create('user_withdraws', function (Blueprint $table) {
            $table->id();
            $table->string('wd_code', 50)->unique('uq_user_withdraw');
            $table->unsignedBigInteger('user_id');
            $table->date('wd_date');
            $table->string('bank_code', 20);
            $table->string('bank_name', 50);
            $table->string('bank_acc_no', 50);
            $table->string('bank_acc_name', 100);
            $table->unsignedSmallInteger('wd_bonus_type');
            $table->unsignedBigInteger('total_bonus');
            $table->unsignedBigInteger('fee');
            $table->unsignedBigInteger('total_transfer');
            $table->unsignedSmallInteger('status')->default(CLAIM_STATUS_PENDING);
            $table->timestamp('status_at')->nullable();
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
        Schema::dropIfExists('user_withdraws');
    }
};
