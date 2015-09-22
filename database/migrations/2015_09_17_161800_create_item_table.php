<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemTable extends Migration
{
	public function up()
	{
		Schema::create('rbac_item', function(Blueprint $table) {
			$table->increments('id');
			$table->string('name', 250);
			$table->integer('type');
			$table->string('description', 250)->nullable();
			$table->string('rule_name', 250)->nullable();
			$table->text('data')->nullable();
			$table->timestamps();

			$table->unique('name');
		});
	}

	public function down()
	{
		Schema::drop('rbac_item');
	}
}