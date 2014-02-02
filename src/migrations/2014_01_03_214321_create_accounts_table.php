<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ka_accounts', function(Blueprint $table)
		{
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('email');
            $table->string('username');
            $table->string('password');

            $table->integer('user_info_id')->nullable()->unsigned();
            $table->foreign('user_info_id')->references('id')->on('ka_user_info')->onDelete('SET NULL')->onUpdate('CASCADE');

            $table->boolean('active')->default(false);

            $table->dateTime('online_at');

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
		Schema::drop('ka_accounts');
	}

}