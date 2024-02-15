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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('user_group');
            $table->unsignedSmallInteger('user_type');
            $table->unsignedSmallInteger('position_id')->default(0);
            $table->unsignedSmallInteger('level_id')->default(0);
            $table->boolean('is_internal')->default(true);
            $table->string('route', 250);
            $table->unsignedBigInteger('created_by');
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
        Schema::dropIfExists('roles');
    }
};
