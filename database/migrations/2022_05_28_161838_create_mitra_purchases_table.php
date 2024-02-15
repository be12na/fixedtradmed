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
        Schema::create('mitra_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique('uq_mpurchase_code');
            $table->unsignedBigInteger('mitra_id');
            // $table->unsignedBigInteger('branch_id')->default(0);
            // $table->unsignedBigInteger('manager_id')->default(0);
            // $table->unsignedBigInteger('referral_id');
            // v2
            $table->unsignedSmallInteger('mitra_type_id')->nullable();
            $table->unsignedSmallInteger('zone_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->unsignedBigInteger('referral_id')->nullable();
            // end v2
            $table->date('purchase_date');
            $table->unsignedBigInteger('savings')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('mitra_note', 250)->nullable();
            $table->string('admin_note', 250)->nullable();
            $table->boolean('is_delivery')->default(false);
            $table->boolean('admin_confirmed')->default(false);
            $table->timestamp('admin_confirmed_at')->nullable();
            $table->boolean('manager_confirmed')->default(false);
            $table->timestamp('manager_confirmed_at')->nullable();
            $table->unsignedSmallInteger('delivery_status')->default(0)->comment('0=belum dikirim, 1=dikirim, 2=diterima, 5=dikembalikan');
            $table->timestamp('delivery_status_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->string('customer_identity', 50)->nullable();
            $table->string('customer_name', 100);
            $table->string('customer_address', 250);
            $table->string('customer_village_id', 11);
            $table->string('customer_village', 60);
            $table->string('customer_district_id', 7);
            $table->string('customer_district', 60);
            $table->string('customer_city_id', 4);
            $table->string('customer_city', 60);
            $table->string('customer_province_id', 2);
            $table->string('customer_province', 60);
            $table->string('customer_pos_code', 6)->nullable();
            $table->string('customer_phone', 15)->nullable();
            $table->boolean('is_transfer')->default(false);
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('bank_code', 30)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('account_no', 100)->nullable();
            $table->string('account_name', 100)->nullable();
            $table->unsignedBigInteger('total_purchase');
            $table->unsignedBigInteger('bonus_persen')->default(0);
            $table->unsignedBigInteger('total_bonus')->default(0);
            $table->unsignedBigInteger('discount_id')->nullable();
            $table->unsignedDecimal('discount_percent', 4, 2)->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            // v2
            $table->unsignedBigInteger('total_zone_discount')->default(0);
            $table->unsignedBigInteger('total_coupon_discount')->default(0);
            $table->string('delivery_from')->nullable();
            $table->string('delivery_to')->nullable();
            $table->unsignedBigInteger('delivery_fee')->default(0);
            // end v2
            $table->unsignedInteger('unique_digit')->default(0);
            $table->unsignedBigInteger('total_transfer')->default(0);
            $table->string('image_transfer')->nullable();
            $table->timestamp('transfer_at')->nullable();
            $table->unsignedSmallInteger('purchase_status')->default(0)->comment('0=pending,1=approved,2=rejected');
            $table->timestamp('status_at')->nullable();
            $table->unsignedBigInteger('status_by')->nullable();
            $table->string('status_note', 250)->nullable();
            $table->string('transfer_note', 250)->nullable();
            $table->string('delivery_code', 50)->nullable();
            $table->boolean('is_posted')->default(false);
            $table->timestamp('posted_at')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->boolean('is_v2')->default(false);
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
        Schema::dropIfExists('mitra_purchases');
    }
};
