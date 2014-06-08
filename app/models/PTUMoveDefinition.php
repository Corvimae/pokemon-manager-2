<?

class PTUMoveDefinition extends Eloquent {
	protected $table = 'ptu_pokemon_moves';

	public function damageBase() {
		return PTUDamageBase::find($this->damage);
	}
	

}

?>