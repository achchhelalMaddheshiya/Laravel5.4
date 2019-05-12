<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPackages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_packages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('package_id');
            $table->unsignedInteger('user_id');
            $table->string('charge_id')->nullable();
            $table->tinyInteger('type')->default(1)->comments('type 1 user , 2 admin , 3 recurring subscription, 4 recurring deduction');
            $table->bigInteger('amount')->default(0);
            $table->string('balance_transaction')->nullable();
            $table->string('stripe_id')->nullable();
            $table->string('stripe_plan')->nullable();
           
            $table->tinyInteger('status')->default(0);

            $table->bigInteger('current_period_start')->default(0);
            $table->bigInteger('current_period_end')->default(0);

            $table->bigInteger('canceled_at')->default(0);
            $table->tinyInteger('cancel_at_period_end')->default(0);
            
            $table->bigInteger('created_at');
            $table->bigInteger('updated_at')->nullable();

        });

        Schema::table('user_packages', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('package_id')->references('id')->on('packages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_packages', function (Blueprint $table) {
            // 1. Drop foreign key constraints
            $table->dropForeign(['user_id']);
            $table->dropForeign(['package_id']);

            $table->dropColumn('user_id');
            $table->dropColumn('package_id');
        });

        Schema::dropIfExists('user_packages');
    }
}
