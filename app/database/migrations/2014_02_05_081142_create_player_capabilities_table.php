<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlayerCapabilitiesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('player_pokemon_capabilities', function($table) {
			$table->increments('id');
			$table->integer('owner');
			$table->integer('capability');
			$table->smallInteger('value');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(){
		Schema::drop('player_pokemon_capabilities');
	}

}
