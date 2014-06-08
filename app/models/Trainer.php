<?

class Trainer extends Eloquent {
	protected $table = 'player_trainers';

	public function pokemon() {
		return $this->hasMany('Pokemon')->orderBy("position");
	}

	public function activePokemon() {
		return $this->hasMany('Pokemon')->where('active', true)->orderBy('position');
	}

	public function user() {
		return User::find($this->user_id);
	}

	public function classes() {
		return $this->hasMany('TrainerClass');
	}
	
	public function campaign() {
		return Campaign::find($this->campaign_id);
	}

	public function belongsToGame($game) {
		return $this->campaign()->id == $game;
	}

	public function primaryCampaign() {
		$pkmn_array = $this->pokemon()->get();
		if(count($pkmn_array) == 0) return false;
		$out = array();
		foreach($pkmn_array as $p) {
			if(!isset($out[$p->legacy])) $out[$p->legacy] = 0;
			$out[$p->legacy]++;
		}
		return array_keys($out, max($out))[0];
	}
}

?>