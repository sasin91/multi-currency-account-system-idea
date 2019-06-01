<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('account_ledger_id')->index();
            $table->foreign('account_ledger_id')->references('id')->on('account_ledgers')->onDelete('CASCADE');
            $table->nullableMorphs('causer');
            $table->decimal('amount');
            $table->decimal('exchange_rate')->comment('The exchange rate value at the time the movement happen.');
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
        Schema::dropIfExists('transactions');
    }
}
