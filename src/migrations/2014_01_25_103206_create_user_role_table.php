<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserRoleTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ka_account_role', function(Blueprint $table)
		{
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->dateTime('from_date');
            $table->dateTime('to_date');

            $table->integer('account_id')->unsigned();
            $table->foreign('account_id')->references('id')->on('ka_accounts')->onDelete('CASCADE')->onUpdate('CASCADE');

            $table->integer('role_id')->unsigned();
            $table->foreign('role_id')->references('id')->on('ka_roles')->onDelete('CASCADE')->onUpdate('CASCADE');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ka_account_role');
	}

}