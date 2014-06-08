<?

class MoveDefinition extends Eloquent {
	protected $table = 'pokemon_moves';

	public function type() {
		return Type::find($this->type);
	}
	
	public function icon() {
		if(!isset($this->type()->name)) return "";
		return "http://cdn.acceptableice.com/pkmn/type-badges/".$this->type()->name.".png";
	}

	public function contestEffect() {
		return ContestEffect::find($this->contest_effect);
	}

	public function PTUDefinition() {
		return $this->hasOne('PTUMoveDefinition');
	}

}

?>