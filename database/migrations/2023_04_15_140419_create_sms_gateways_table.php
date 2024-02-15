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
        Schema::create('sms_settings', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint', 250);
            $table->string('vendor', 50);
            $table->unsignedInteger('vendor_type');
            $table->string('user_key', 100)->nullable();
            $table->string('pass_key', 100)->nullable();
            $table->string('sms_token', 250)->nullable();
            $table->boolean('is_token')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('sms_errors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('phone', 15);
            $table->string('endpoint', 250);
            $table->string('vendor', 50);
            $table->unsignedInteger('vendor_type');
            $table->string('user_key', 100)->nullable();
            $table->string('pass_key', 100)->nullable();
            $table->string('sms_token', 250)->nullable();
            $table->boolean('is_token')->default(false);
            $table->text('send_message');
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms_errors');
        Schema::dropIfExists('sms_settings');
    }
};
