<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemChildTable extends Migration
{
	public function up()
	{
		Schema::create('rbac_item_child', function(Blueprint $table) {
			$table->increments('id');
			$table->string('parent', 250);
			$table->string('child', 250);
		});
	}

	public function down()
	{
		Schema::drop('rbac_item_child');
	}
}