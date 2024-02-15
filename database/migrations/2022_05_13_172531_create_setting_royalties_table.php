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
        Schema::create('setting_royalties', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('position_id');
            $table->boolean('is_internal')->default(true);
            $table->boolean('is_network')->default(true);
            $table->unsignedDecimal('percent', 4, 2);
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
        Schema::dropIfExists('setting_royalties');
    }
};
