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
        Schema::create('mitra_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('point')->unique('uq_rwd_point');
            $table->string('reward', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('mitra_reward_claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('reward_id');
            $table->unsignedSmallInteger('status')->default(CLAIM_STATUS_PENDING);
            $table->timestamp('status_at')->nullable();
            $table->string('status_note', 250)->nullable();
            $table->unsignedBigInteger('status_by')->nullable();
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
        Schema::dropIfExists('mitra_reward_claims');
        Schema::dropIfExists('mitra_rewards');
    }
};
