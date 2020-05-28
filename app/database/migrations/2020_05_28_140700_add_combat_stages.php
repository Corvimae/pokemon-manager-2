<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCombatStages extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('player_pokemon_combat_stages', function($table) {
      $table->increments('id');
      $table->integer('pokemon_id');
			$table->smallInteger('attack');
			$table->smallInteger('defense');
			$table->smallInteger('spattack');
			$table->smallInteger('spdefense');
			$table->smallInteger('speed');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('player_pokemon_combat_stages');

	}
}
`