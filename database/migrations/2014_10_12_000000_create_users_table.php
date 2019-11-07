<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')-> unique();
            $table->timestamp('email_verified_at')-> nullable();
            $table->string('password');
            $table->string('voice');
            $table->bigInteger('vk_id');
            $table->string('access_token_user');
            $table->bigInteger('group_id_index');
            $table->bigInteger('group_id_second');
            $table->string('access_token_group');
            $table->string('confirmation_code');
            $table->string('secret_key') ->default(rand());
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
