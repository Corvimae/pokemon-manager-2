<?

class Nature extends Eloquent {
	protected $table = 'pokemon_natures';

	public function upStat() {
		return PokemonUtils::convertIntToStat($this->up);
	}

	public function downStat() {
		return PokemonUtils::convertIntToStat($this->down);
	}


}

?>