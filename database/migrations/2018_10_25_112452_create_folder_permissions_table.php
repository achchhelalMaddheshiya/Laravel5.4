<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFolderPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('folder_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('folder_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('permission_id');
            $table->tinyInteger('status')->default(0);
            $table->bigInteger('created_at');
            $table->bigInteger('updated_at')->nullable();
        });

        Schema::table('folder_permissions', function (Blueprint $table) {
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::table('folder_permissions', function (Blueprint $table) {

            // 1. Drop foreign key constraints
            $table->dropForeign(['folder_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['permission_id']);


            $table->dropColumn('folder_id');
            $table->dropColumn('user_id');
            $table->dropColumn('permission_id');
        });

        Schema::dropIfExists('folder_permissions');
    }
}
