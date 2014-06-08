<?

class TrainerClassDefinition extends Eloquent {
	protected $table = 'trainer_classes';

	public function features() {
		return $this->hasMany('TrainerFeatureDefinition');
	}

}

?>