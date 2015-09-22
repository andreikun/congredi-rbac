<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRuleTable extends Migration
{
	public function up()
	{
		Schema::create('rbac_rule', function(Blueprint $table) {
			$table->increments('id');
			$table->string('name', 250);
			$table->text('data')->nullable();
			$table->timestamps();

			$table->unique('name');
		});
	}

	public function down()
	{
		Schema::drop('rbac_rule');
	}
}