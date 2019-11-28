<?php

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
}

?>