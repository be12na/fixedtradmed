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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 30)->unique();
            $table->string('password', 255);
            $table->string('name', 100);
            $table->string('email', 100);
            $table->unsignedBigInteger('referral_id')->nullable();

            $table->boolean('is_login')->default(true);
            $table->unsignedSmallInteger('user_group');
            $table->unsignedSmallInteger('user_type');
            $table->unsignedSmallInteger('division_id')->default(0);

            $table->smallInteger('position_int')->nullable();
            $table->smallInteger('manager_type')->nullable();
            $table->smallInteger('position_ext')->nullable();
            $table->unsignedBigInteger('upline_id')->nullable();
            $table->smallInteger('level_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->boolean('branch_manager')->default(false);

            $table->unsignedSmallInteger('user_status')->default(USER_STATUS_ACTIVE);
            $table->timestamp('status_at')->nullable();
            $table->json('status_logs')->nullable();
            $table->boolean('activated')->default(false);
            $table->timestamp('activated_at')->nullable();

            $table->string('phone', 15)->nullable();
            $table->unsignedInteger('mitra_type')->nullable();
            $table->string('sub_domain', 100)->nullable();
            $table->string('market_name', 100)->nullable();

            $table->boolean('is_profile')->default(false);
            $table->timestamp('profile_at')->nullable();
            $table->string('image_profile', 50)->nullable();

            $table->string('identity', 50)->nullable();
            $table->string('address', 250)->nullable();
            $table->string('village_id', 11)->nullable();
            $table->string('village', 60)->nullable();
            $table->string('district_id', 7)->nullable();
            $table->string('district', 60)->nullable();
            $table->string('city_id', 4)->nullable();
            $table->string('city', 60)->nullable();
            $table->string('province_id', 2)->nullable();
            $table->string('province', 60)->nullable();
            $table->string('pos_code', 6)->nullable();

            $table->boolean('valid_id')->default(true);

            $table->string('contact_address', 250)->nullable();
            $table->string('contact_village_id', 11)->nullable();
            $table->string('contact_village', 60)->nullable();
            $table->string('contact_district_id', 7)->nullable();
            $table->string('contact_district', 60)->nullable();
            $table->string('contact_city_id', 4)->nullable();
            $table->string('contact_city', 60)->nullable();
            $table->string('contact_province_id', 2)->nullable();
            $table->string('contact_province', 60)->nullable();
            $table->string('contact_pos_code', 6)->nullable();

            $table->json('roles')->nullable();

            $table->boolean('reg_by_ref')->default(false);
            $table->unsignedSmallInteger('mitra_type_reg')->nullable();

            $table->string('session_id')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
