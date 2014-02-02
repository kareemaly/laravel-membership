<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleRequestsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ka_role_requests', function(Blueprint $table)
		{
            $table->engine = 'InnoDB';
			$table->increments('id');

            $table->text('description');
            $table->string('status')->default(\Kareem3d\Membership\RoleRequest::PENDING);

            $table->integer('role_id')->unsigned();
            $table->foreign('role_id')->references('id')->on('ka_roles')->onDelete('CASCADE')->onUpdate('CASCADE');

            $table->integer('account_id')->unsigned();
            $table->foreign('account_id')->references('id')->on('ka_roles')->onDelete('CASCADE')->onUpdate('CASCADE');

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
		Schema::drop('ka_role_requests');
	}

}
