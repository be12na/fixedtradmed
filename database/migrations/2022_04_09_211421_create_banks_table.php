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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('bank_code', 30);
            $table->string('bank_name', 100);
            $table->string('account_no', 100);
            $table->string('account_name', 100);
            $table->unsignedSmallInteger('bank_type')->default(10)->comment('1 = perusahaan, 10 = member');
            $table->boolean('is_active')->default(true);
            $table->timestamp('active_at')->nullable();
            $table->unsignedBigInteger('active_by')->nullable();
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
        Schema::dropIfExists('banks');
    }
};
