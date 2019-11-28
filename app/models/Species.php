<?php

class Species extends Eloquent {
	protected $table = 'pokemon_reference';

	public function sprite() {
		return "https://play.pokemonshowdown.com/sprites/dex/".strtolower($this->name).".png";
	}

	public function animatedSprite() {
		return "https://play.pokemonshowdown.com/sprites/ani/".strtolower($this->name).".gif";
	}
}

?>