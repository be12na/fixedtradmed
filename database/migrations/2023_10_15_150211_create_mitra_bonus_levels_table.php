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
        Schema::create('mitra_bonus_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('type');
            $table->unsignedInteger('level');
            $table->string('code', 5)->unique('uq_bonus_level_code');
            $table->string('name', 50);
            $table->unsignedBigInteger('bonus');
            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('mitra_bonus_levels');
    }
};
