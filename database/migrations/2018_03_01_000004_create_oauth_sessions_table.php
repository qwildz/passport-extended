<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauth_sessions', function (Blueprint $table) {
            $table->string('id', 100);
            $table->string('token_id', 100)->primary();
            $table->boolean('revoked');
            $table->timestamps();
            $table->dateTime('revoked_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('oauth_sessions');
    }
}
