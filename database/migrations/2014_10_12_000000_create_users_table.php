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
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('social_id')->nullable();
            $table->string('image_url')->nullable();
            $table->string('login_type')->default('facebook')->nullable();
            $table->string('password');
            $table->boolean('blocked')->default(0);
            $table->string('timezone')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->unique(['login_type', 'social_id']);
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
