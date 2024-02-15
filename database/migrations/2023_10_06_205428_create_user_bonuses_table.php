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
        Schema::create('user_bonuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('from_user_id')->nullable();
            $table->unsignedInteger('bonus_type');
            $table->date('bonus_date');
            $table->unsignedBigInteger('bonus_amount');
            $table->boolean('should_upgrade')->default(false);
            $table->boolean('should_ro')->default(false);
            $table->unsignedBigInteger('ro_id')->nullable();
            $table->unsignedBigInteger('wd_id')->nullable();
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
        Schema::dropIfExists('user_bonuses');
    }
};
