<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->comment('UUID is used as an account reference, for performance reasons it is not used for DB indexes.');
            $table->unsignedBigInteger('owner_id')->index();
            $table->foreign('owner_id')->references('id')->on('users');
            $table->integer('points')->default(0);
            $table->string('type')->default(\App\Enums\AccountType::MAIN);
            $table->string('description')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('accounts');
    }
}
