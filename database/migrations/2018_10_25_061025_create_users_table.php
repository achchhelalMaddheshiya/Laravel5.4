<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('role_id');
            $table->string('slug')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('secondary_email')->nullable();
            
            $table->string('password');
            $table->string('image')->nullable();
            $table->string('forgot_token')->nullable();
            $table->string('verify_token')->nullable();
            $table->string('email_verification_code')->nullable();
            $table->string('forgot_password_token')->nullable();
            $table->bigInteger('forgot_token_expiry')->nullable();
            $table->string('auth_token');
            $table->tinyInteger('primary_declaration')->default(0);
            $table->tinyInteger('guarantee_declaration')->default(0);            
            $table->bigInteger('declaration_date')->nullable();

            $table->tinyInteger('temp_primary_declaration')->default(0);
            $table->tinyInteger('temp_guarantee_declaration')->default(0);            
            $table->bigInteger('temp_declaration_date')->nullable();

            $table->tinyInteger('status')->default(0);
            $table->rememberToken();

            $table->string('stripe_id')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('card_last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            
            $table->bigInteger('created_at');
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('secondary_expiry')->nullable();
        });

        Schema::table('users', function($table) {
            $table->foreign('role_id')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // 1. Drop foreign key constraints
            $table->dropForeign(['role_id']);
            
            $table->dropColumn('role_id');
        });
        Schema::dropIfExists('users');
    }
}
