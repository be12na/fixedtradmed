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
        Schema::create('branch_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique('uq_transfercode');
            $table->date('transfer_date');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('manager_id');
            $table->unsignedSmallInteger('manager_position')->nullable();
            $table->unsignedSmallInteger('manager_type')->nullable();
            $table->unsignedBigInteger('bank_id');
            $table->string('bank_code', 30);
            $table->string('bank_name', 100);
            $table->string('account_no', 100);
            $table->string('account_name', 100);
            $table->unsignedBigInteger('total_omzets');
            $table->unsignedBigInteger('total_crews');
            $table->unsignedBigInteger('total_foundations');
            $table->unsignedBigInteger('total_savings');
            $table->unsignedBigInteger('sub_total_sales');
            $table->unsignedDecimal('discount_persen', 4, 2)->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('omzet_used')->default(0);
            $table->unsignedBigInteger('sub_total');
            $table->unsignedInteger('unique_digit');
            $table->unsignedBigInteger('total_transfer');
            $table->string('image_transfer')->nullable();
            $table->timestamp('transfer_at')->nullable();
            $table->unsignedSmallInteger('transfer_status')->default(0)->comment('0=pending,1=approved,2=rejected');
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
        Schema::dropIfExists('branch_transfers');
    }
};
