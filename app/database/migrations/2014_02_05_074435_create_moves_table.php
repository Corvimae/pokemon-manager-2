<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMovesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('pokemon_moves', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->smallInteger('type');
			$table->string('damage');
			$table->string('frequency');
			$table->smallInteger('ac');
			$table->smallInteger('attack_type');
			$table->string('attack_range');
			$table->string('effects');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('pokemon_moves');
	}

}
