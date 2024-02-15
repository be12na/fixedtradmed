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
        Schema::create('user_packages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique('uq_usrpkg_code');
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('package_id');
            $table->unsignedBigInteger('price');
            $table->unsignedInteger('digit')->default(0);
            $table->unsignedBigInteger('total_price');
            $table->unsignedSmallInteger('status')->default(MITRA_PKG_PENDING);
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('bank_code', 30)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('account_no', 100)->nullable();
            $table->string('account_name', 100)->nullable();
            $table->string('image', 50)->nullable();
            $table->timestamp('transfer_at')->nullable();
            $table->timestamp('confirm_at')->nullable();
            $table->timestamp('reject_at')->nullable();
            $table->timestamp('cancel_at')->nullable();
            $table->string('note', 250)->nullable();
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
        Schema::dropIfExists('user_packages');
    }
};
