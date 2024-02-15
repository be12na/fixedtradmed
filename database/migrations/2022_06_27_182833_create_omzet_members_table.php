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
        Schema::create('omzet_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('user_id');
            $table->date('omzet_date');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('transfer_id');
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('salesman_id');
            $table->json('salesman_ancestors')->comment('hanya untuk menyimpan history structure');
            $table->boolean('is_from_mitra')->default(false);
            $table->unsignedInteger('qty_box');
            $table->unsignedInteger('qty_pcs');
            $table->unsignedBigInteger('omzet');
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
        Schema::dropIfExists('omzet_members');
    }
};
