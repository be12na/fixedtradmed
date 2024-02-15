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
        Schema::create('branch_sales', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique('uq_brsale_code');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('manager_id');
            $table->unsignedSmallInteger('manager_position')->nullable();
            $table->unsignedSmallInteger('manager_type')->nullable();
            $table->unsignedBigInteger('salesman_id');
            $table->unsignedSmallInteger('salesman_position')->nullable();
            $table->date('sale_date');
            $table->unsignedBigInteger('savings')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('salesman_note', 250)->nullable();
            $table->boolean('is_posted')->default(false);
            $table->timestamp('posted_at')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable();
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
        Schema::dropIfExists('branch_sales');
    }
};
