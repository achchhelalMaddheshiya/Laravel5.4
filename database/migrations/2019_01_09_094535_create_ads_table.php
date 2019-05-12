<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('category_id');
            $table->string('link'); 
            $table->string('file'); 
            $table->tinyInteger('status')->default(0);
            $table->bigInteger('created_at');
            $table->bigInteger('updated_at')->nullable();
        });

        Schema::table('ads', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ads', function (Blueprint $table) {
            // 1. Drop foreign key constraints
            $table->dropForeign(['category_id']);
            
            $table->dropColumn('category_id');
        });

        Schema::dropIfExists('ads');
    }
}
