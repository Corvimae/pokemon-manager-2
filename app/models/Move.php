<?php

class Move extends Eloquent {
	protected $table = 'player_pokemon_moves';

	public function definition() {
		return MoveDefinition::find($this->move);
	}

	public function type() {
		if($this->type_override != 0) {
			return Type::where("id", $this->type_override)->first();
		}
		return $this->definition()->type();
	}

	public function icon() {
		if(!isset($this->type()->name)) return "";
		return "/images/types/".strtolower($this->type()->name).".png";
	}

}

?>