<?

class Move extends Eloquent {
	protected $table = 'player_pokemon_moves';

	public function definition() {
		return MoveDefinition::find($this->move);
	}

	public function type() {
		if($this->typeOverride != 0) {
			return Type::where("id", $this->typeOverride)->first();
		}
		return $this->definition()->type();
	}

	public function icon() {
		if(!isset($this->type()->name)) return "";
		return "http://cdn.acceptableice.com/pkmn/type-badges/".$this->type()->name.".png";
	}

}

?>