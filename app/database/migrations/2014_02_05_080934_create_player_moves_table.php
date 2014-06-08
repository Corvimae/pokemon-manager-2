<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlayerMovesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('player_pokemon_moves', function($table) {
			$table->increments('id');
			$table->integer('owner');
			$table->integer('move');
			$table->boolean('isTutor');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(){
		Schema::drop('player_pokemon_moves');
	}

}
