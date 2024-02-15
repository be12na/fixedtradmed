<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStructuresTableMigration extends Migration
{
    public function up()
    {
        Schema::create('structures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique('uq_user_structure');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedInteger('position');
            $table->softDeletes();

            // $table->foreign('parent_id')
            //     ->references('id')
            //     ->on('structures')
            //     ->onDelete('set null');
        });

        Schema::create('structures_closure', function (Blueprint $table) {
            $table->id('closure_id');
            $table->unsignedBigInteger('ancestor');
            $table->unsignedBigInteger('descendant');
            $table->unsignedInteger('depth');

            // $table->foreign('ancestor')
            //     ->references('id')
            //     ->on('structures')
            //     ->onDelete('cascade');

            // $table->foreign('descendant')
            //     ->references('id')
            //     ->on('structures')
            //     ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('structures_closure');
        Schema::dropIfExists('structures');
    }
}
