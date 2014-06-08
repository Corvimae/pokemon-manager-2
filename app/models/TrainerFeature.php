<?

class TrainerFeature extends Eloquent {
	protected $table = 'player_trainer_features';

	public function definition() {
		return TrainerFeatureDefinition::find($this->feature_id);
	}

}

?>