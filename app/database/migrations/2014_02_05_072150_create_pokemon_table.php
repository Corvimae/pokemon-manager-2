<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePokemonTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('player_pokemon_data', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->integer('owner');
			$table->smallInteger('species');
			$table->integer('experience');
			$table->smallInteger('nature');
			$table->smallInteger('gender');
			$table->smallInteger('type1');
			$table->smallInteger('type2');
			$table->text('notes');
			$table->integer('current_health');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('player_pokemon_data');
	}

}
