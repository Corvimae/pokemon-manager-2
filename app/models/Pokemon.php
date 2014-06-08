<?

class Pokemon extends Eloquent {
	protected $table = 'player_pokemon_data';

	public function owner() {
		return User::find($this->user_id);
	}

	public function trainer() {
		return Trainer::find($this->trainer_id);
	}
	
	public function type1() {
		return Type::find($this->type1);
	}

	public function type2() {
		return Type::find($this->type2);
	}

	public function moves() {
		return $this->hasMany('Move')->orderBy("position");
	}

	public function abilities() {
		return $this->hasMany('Ability');
	}

	public function capabilitieS() {
		return $this->hasMany("Capability")->orderBy("position");
	}

	public function species() {
		return Species::find($this->species);
	}

	public function nature() {
		return Nature::find($this->nature);
	}

	public function heldItem() {
		return HeldItem::find($this->held_item);
	}

	public function baseStats() {
		return $this->stats()->where('isBase', '=', '1')->first();
	}

	public function addStats() {
		return $this->stats()->where('isBase', '=', '0')->first();
	}



	public function totalStats() {
		$stats = new StatList();
		$stats->hp = $this->baseStats()->hp + $this->addStats()->hp;
		$stats->attack = $this->baseStats()->attack + $this->addStats()->attack;
		$stats->defense = $this->baseStats()->defense + $this->addStats()->defense;
		$stats->spattack = $this->baseStats()->spattack + $this->addStats()->spattack;
		$stats->spdefense = $this->baseStats()->spdefense + $this->addStats()->spdefense;
		$stats->speed = $this->baseStats()->speed + $this->addStats()->speed;

		return $stats;
	}

	public function attackEvasion() {
		return min(min(floor($this->totalStats()->defense/5), 6),9);
	}

	public function specialAttackEvasion() {
		return min(min(floor($this->totalStats()->spdefense/5), 6),9);
	}

	public function speedEvasion() {
		return min(floor($this->totalStats()->speed/10), 6);;
	}

	public function stabModifier() {
		return floor($this->level()/5);
	}

	public function maxHealth() {
		return 2*$this->level() + $this->totalStats()->hp*4;
	}

	public function stats() {
		return $this->hasMany('StatList');
	}

	public function getOffensiveTypeEffectivenessStrings() {
		$t1 = $this->type1()->getOffensiveEffectivenessStrings();
		$t2 = $this->type2()->getOffensiveEffectivenessStrings();	
	}

	public function getCombinedOffensiveTypeEffectiveness() {
		return Type::getCombinedOffensiveEffectiveness($this->type1(), $this->type2());
	}

	public function getCombinedOffensiveTypeEffectivenessStrings() {
		return Type::getCombinedOffensiveEffectivenessStrings($this->type1(), $this->type2());
	}

	public function getCombinedDefensiveTypeEffectiveness() {
		return Type::getCombinedDefensiveEffectiveness($this->type1(), $this->type2());
	}

	public function getCombinedDefensiveTypeEffectivenessStrings() {
		return Type::getCombinedDefensiveEffectivenessStrings($this->type1(), $this->type2());
	}


	public function getModifierForStat($stat) {
		if($stat < 10) return (($stat + ($stat % 2) - 10)/2);
		return ($stat - ($stat % 2) - 10)/2;
	}



	public function level() {
		$xp = $this->experience;
		if(!is_null($this->trainer()) && $this->trainer()->campaign()->isPTU) return $this->calculatePTULevel($xp);
		$level = min(100, 1+floor($xp/25)*($xp<50)+2*($xp>=50)+floor(($xp-50)/50)*($xp>50)*($xp<200)+3*($xp>=200)+floor(($xp-200)/200)*($xp>200)*($xp<1000)+4*($xp>=1000)+floor(($xp-1000)/500)*($xp>1000)*($xp<2000)+2*($xp>=2000)
				+floor(($xp-2000)/1000)*($xp>2000)*($xp<10000)+8*($xp>=10000)+floor(($xp-10000)/1500)*($xp>10000)*($xp<25000)+10*($xp>=25000)+floor(($xp-25000)/2500)*($xp>25000)*($xp<50000)+10*($xp>=50000)
				+floor(($xp-50000)/5000)*($xp>50000)*($xp<100000)+10*($xp>=100000)+floor(($xp-100000)/10000)*($xp>100000));
		return $level;
	}
	
	public function campaign() {
		if(is_null($this->trainer())) return Campaign::find(0);
		return $this->trainer()->campaign();
	}
	function calculatePTULevel($xp){
	    if($xp < 0) return 0;
        if($xp < 10) return 1;
        if($xp < 20) return 2;
        if($xp < 30) return 3;
        if($xp < 40) return 4;
        if($xp < 50) return 5;
        if($xp < 60) return 6;
        if($xp < 70) return 7;
        if($xp < 80) return 8;
        if($xp < 90) return 9;
        if($xp < 110) return 10;
        if($xp < 135) return 11;
        if($xp < 160) return 12;
        if($xp < 190) return 13;
        if($xp < 220) return 14;
        if($xp < 250) return 15;
        if($xp < 285) return 16;
        if($xp < 320) return 17;
        if($xp < 360) return 20;
        if($xp < 400) return 21;
        if($xp < 460) return 22;
        if($xp < 530) return 23;
        if($xp < 600) return 24;
        if($xp < 670) return 25;
        if($xp < 745) return 26;
        if($xp < 820) return 27;
        if($xp < 900) return 28;
        if($xp < 990) return 29;
        if($xp < 1075) return 30;
        if($xp < 1165) return 31;
        if($xp < 1260) return 32;
        if($xp < 1355) return 33;
        if($xp < 1455) return 34;
        if($xp < 1555) return 35;
        if($xp < 1660) return 36;
        if($xp < 1770) return 37;
        if($xp < 1880) return 38;
        if($xp < 1995) return 39;
        if($xp < 2110) return 40;
        if($xp < 2230) return 41;
        if($xp < 2355) return 42;
        if($xp < 2480) return 43;
        if($xp < 2610) return 44;
        if($xp < 2740) return 45;
        if($xp < 2875) return 46;
        if($xp < 3015) return 47;
        if($xp < 3155) return 48;
        if($xp < 3300) return 49;
        if($xp < 3445) return 50;
        if($xp < 3645) return 51;
        if($xp < 3850) return 52;
        if($xp < 4060) return 53;
        if($xp < 4270) return 54;
        if($xp < 4485) return 55;
        if($xp < 4705) return 56;
        if($xp < 4930) return 57;
        if($xp < 5160) return 58;
        if($xp < 5390) return 59;
        if($xp < 5625) return 60;
        if($xp < 5865) return 61;
        if($xp < 6110) return 62;
        if($xp < 6360) return 63;
        if($xp < 6610) return 64;
        if($xp < 6865) return 65;
        if($xp < 7125) return 66;
        if($xp < 7390) return 67;
        if($xp < 7660) return 68;
        if($xp < 7925) return 69;
        if($xp < 8205) return 70;
        if($xp < 8485) return 71;
        if($xp < 8770) return 72;
        if($xp < 9060) return 73;
        if($xp < 9350) return 74;
        if($xp < 9645) return 75;
        if($xp < 9945) return 76;
        if($xp < 10250) return 77;
        if($xp < 10560) return 78;
        if($xp < 10870) return 79;
        if($xp < 11185) return 80;
        if($xp < 11505) return 81;
        if($xp < 11910) return 82;
        if($xp < 12320) return 83;
        if($xp < 12735) return 84;
        if($xp < 13155) return 85;
        if($xp < 13580) return 86;
        if($xp < 14010) return 87;
        if($xp < 14445) return 88;
        if($xp < 14885) return 89;
        if($xp < 15330) return 90;
        if($xp < 15780) return 91;
        if($xp < 16235) return 92;
        if($xp < 16695) return 93;
        if($xp < 17160) return 94;
        if($xp < 17630) return 95;
        if($xp < 18105) return 96;
        if($xp < 18585) return 97;
        if($xp < 19070) return 98;
        if($xp < 19560) return 99;
        if($xp < 20055) return 99;
        if($xp >= 20055) return 100;
        return 0;
}

	public function gender() {
		switch($this->gender) {
			case 0: return "Male";
			case 1: return "Female";
			default: return "None";
		}
	}
}

?>