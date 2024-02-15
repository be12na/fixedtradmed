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
        Schema::create('branch_payments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique('uq_payment_code');
            $table->date('payment_date');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('manager_id');
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('bank_code', 30)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('account_no', 100)->nullable();
            $table->string('account_name', 100)->nullable();
            $table->unsignedBigInteger('total_price');
            $table->unsignedBigInteger('total_discount');
            $table->unsignedBigInteger('sub_total');
            $table->unsignedInteger('unique_digit');
            $table->unsignedBigInteger('total_transfer');
            $table->string('image_transfer')->nullable();
            $table->timestamp('transfer_at')->nullable();
            $table->string('payment_note', 250)->nullable();
            $table->unsignedSmallInteger('transfer_status')->default(PROCESS_STATUS_PENDING);
            $table->timestamp('status_at')->nullable();
            $table->unsignedBigInteger('status_by')->nullable();
            $table->string('status_note', 250)->nullable();
            $table->string('transfer_note', 250)->nullable();
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
        Schema::dropIfExists('branch_payments');
    }
};
