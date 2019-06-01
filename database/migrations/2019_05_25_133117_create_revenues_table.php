<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRevenuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('revenues', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('account_id')->nullable()->index();
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->string('customer_email')->nullable();
            $table->decimal('amount');
            $table->decimal('currency_rate');
            $table->string('currency_code');
            $table->string('category')->nullable()->comment('Category can be used to easily group by.');
            $table->text('description')->nullable();
            $table->string('payment_method')->comment('Name of the payment method we used. eg. Bank');
            $table->string('reference')->nullable();
            $table->date('paid_at')->nullable()->comment('Eg. a Bank Transfer may be created by not received before next business day.');
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
        Schema::dropIfExists('revenues');
    }
}
