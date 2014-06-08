<?

class Capability extends Eloquent {
	protected $table = 'player_pokemon_capabilities';

	public function definition() {
		return CapabilityDefinition::find($this->capability);
	}

}

?>