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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique('uq_branchcode');
            $table->string('name', 100);
            $table->smallInteger('wilayah')->nullable();
            $table->string('address', 250)->nullable();
            $table->string('pos_code', 6)->nullable();
            $table->string('telp', 15)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('active_at')->nullable();
            $table->unsignedBigInteger('active_by')->nullable();
            $table->boolean('is_stock')->default(false);
            $table->unsignedSmallInteger('zone_id')->nullable();
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
        Schema::dropIfExists('branches');
    }
};
