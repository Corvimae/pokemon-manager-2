<?

class Ability extends Eloquent {
	protected $table = 'player_pokemon_abilities';

	public function definition() {
		return AbilityDefinition::find($this->ability);
	}

}

?>