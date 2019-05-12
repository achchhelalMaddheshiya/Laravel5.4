<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sender_id')->nullable();
            $table->unsignedInteger('receiver_id')->nullable();
            $table->unsignedInteger('folder_id')->nullable();
            $table->unsignedInteger('invited_by')->nullable();
            $table->string('notification_type');
            $table->string('email')->nullable()->comment('refernce for receiver id');
            $table->unsignedInteger('family_id')->nullable();
            $table->tinyInteger('is_read')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->bigInteger('created_at');
            $table->bigInteger('updated_at')->nullable();
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('invited_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('family_id')->references('id')->on('family_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            // 1. Drop foreign key constraints
            $table->dropForeign(['sender_id']);
            $table->dropForeign(['receiver_id']);
            $table->dropForeign(['family_id']);
            $table->dropForeign(['invited_by']);
            

            $table->dropColumn(['sender_id','receiver_id','family_id','invited_by','folder_id']);
        });

        Schema::dropIfExists('notifications');
    }
}
