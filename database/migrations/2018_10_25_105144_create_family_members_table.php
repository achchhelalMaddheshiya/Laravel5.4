<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFamilyMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('family_members', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('invited_by');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->bigInteger('dob')->default(0);
            $table->unsignedInteger('family_id');
            $table->unsignedInteger('relation');
            $table->string('location')->nullable();
            $table->decimal('lng', 11, 6);
            $table->decimal('lat', 11, 6);
            $table->string('code')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0 - pending, 1- Accepted, 2- Rejected');
            $table->bigInteger('created_at');
            $table->bigInteger('updated_at')->nullable();
        });

        Schema::table('family_members', function (Blueprint $table) {
            $table->foreign('invited_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('family_id')->references('id')->on('family_types');
            $table->foreign('relation')->references('id')->on('relations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       
        Schema::table('family_members', function (Blueprint $table) {
            // 1. Drop foreign key constraints
            $table->dropForeign(['invited_by']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['family_id']);
            $table->dropForeign(['relation']);

            $table->dropColumn('invited_by');
            $table->dropColumn('user_id');
            $table->dropColumn('family_id');
            $table->dropColumn('relation');
        });

        Schema::dropIfExists('family_members');
    }
}
