<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');

            $table->string('stripe_customer_id'); 
            $table->string('card_id'); 
            $table->string('card_last_digit'); 

            $table->tinyInteger('status')->default(0);

            $table->bigInteger('created_at');
            $table->bigInteger('updated_at')->nullable();
        });

        Schema::table('user_cards', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_cards', function (Blueprint $table) {
            // 1. Drop foreign key constraints
            $table->dropForeign(['user_id']);
            
            $table->dropColumn('user_id');
        });

        Schema::dropIfExists('user_cards');
    }
}
