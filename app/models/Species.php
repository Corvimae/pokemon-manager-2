<?php

class Species extends Eloquent {
	protected $table = 'pokemon_reference';

	public function sprite() {
		return "https://play.pokemonshowdown.com/sprites/gen5/".str_replace('-', '', strtolower($this->name)).".png";
	}

	public function animatedSprite() {
		// if($this->id >= 810) return $this->sprite();
		
		return "https://play.pokemonshowdown.com/sprites/ani/".str_replace('-', '', strtolower($this->name)).".gif";
	}
}

?>