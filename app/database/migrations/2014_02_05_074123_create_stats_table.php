<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('player_pokemon_stats', function($table) {
			$table->increments('id');
			$table->integer('owner');
			$table->smallInteger('hp');
			$table->smallInteger('attack');
			$table->smallInteger('defense');
			$table->smallInteger('spattack');
			$table->smallInteger('spdefense');
			$table->smallInteger('speed');
			$table->boolean('isBase');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(){
		Schema::drop('player_pokemon_stats');
	}

}
