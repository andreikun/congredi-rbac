<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssignmentTable extends Migration
{
	public function up()
	{
		Schema::create('rbac_assignment', function(Blueprint $table) {
			$table->increments('id');
			$table->string('item_name', 250);
			$table->integer('user_id');
			$table->timestamp('created_at');
		});
	}

	public function down()
	{
		Schema::drop('rbac_assignment');
	}
}