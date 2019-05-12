<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->string('description')->nullable();
            $table->integer('audio_limit')->default(0);
            $table->integer('video_limit')->default(0);
            $table->integer('document_limit')->default(0);
            $table->integer('image_limit')->default(0);
            $table->integer('members_count_limit')->default(0);
            $table->double('amount')->default(0);
            $table->integer('duration')->default(0);
            $table->integer('size')->default(0);
            $table->integer('subscription_days')->default(0);
            $table->integer('status')->default(0);
            $table->bigInteger('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('packages');
    }
}
