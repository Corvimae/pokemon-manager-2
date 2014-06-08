<?

class Species extends Eloquent {
	protected $table = 'pokemon_reference';

	public function sprite() {
		return "http://cdn.acceptableice.com/pkmn/sprites/".$this->id.".png";
	}

	public function animatedSprite() {
		return "http://cdn.acceptableice.com/pkmn/anim/".strtolower($this->name).".gif";
	}
}

?>