<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ad_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ad_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('ip')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->bigInteger('created_at');
            $table->bigInteger('updated_at')->nullable();
        });
        
        Schema::table('ad_stats', function (Blueprint $table) {
            $table->foreign('ad_id')->references('id')->on('ads');
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
        Schema::table('ad_stats', function (Blueprint $table) {
            // 1. Drop foreign key constraints
            $table->dropForeign(['ad_id']);
            $table->dropForeign(['user_id']);
            
            $table->dropColumn('ad_id');
            $table->dropColumn('user_id');
        });

        Schema::dropIfExists('ad_stats');
    }
}
