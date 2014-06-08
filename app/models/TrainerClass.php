<?

class TrainerClass extends Eloquent {
	protected $table = 'player_trainer_classes';

	public function trainer() {
		return $this->belongsTo('Trainer');
	}
	public function definition() {
		return TrainerClassDefinition::find($this->class_id);
	}

	public function features() {
		return $this->hasMany('TrainerClass');
	}

}

?>