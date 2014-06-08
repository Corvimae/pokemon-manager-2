<?

class AbilityDefinition extends Eloquent {
	protected $table = 'pokemon_abilities';

 	public function PTUDefinition() {
 		return $this->hasOne('PTUAbilityDefinition');
 	}

}

?>