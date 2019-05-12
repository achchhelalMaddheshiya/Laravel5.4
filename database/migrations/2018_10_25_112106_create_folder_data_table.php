<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFolderDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('folder_data', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('folder_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('attribute_types'); // links,photos,videos,documents,location_gone,location_want,password_fb,password_gm,password_lknd,password_tw
            $table->string('meta_key')->nullable();
            $table->string('meta_value')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_link')->nullable();
            $table->string('file')->nullable();
            $table->string('extension')->nullable();
            $table->decimal('lng', 10, 7);
            $table->decimal('lat', 10, 7);
            $table->tinyInteger('status')->default(0);
            $table->bigInteger('created_at');
            $table->bigInteger('updated_at')->nullable();
        });

        Schema::table('folder_data', function (Blueprint $table) {
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('folder_data', function (Blueprint $table) {
            // 1. Drop foreign key constraints
            $table->dropForeign(['folder_id']);
            $table->dropColumn('folder_id');
        });
        Schema::dropIfExists('folder_data');
    }
}
