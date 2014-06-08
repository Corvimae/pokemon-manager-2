<?

class Type extends Eloquent {
	protected $table = 'pokemon_types';

	public static $typeChart = array(
		array(0.5, 1, 0.5, 1, 2, 0.5, 0.5, 1, 2, 1, 2, 1, 0.5, 0.5, 1, 1, 0.5, 1),
		array(1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0.5, 1, 1, 1, 0, 1, 0.5, 1),
		array(2, 1, 0.5, 1, 0.5, 1, 1, 1, 1, 1, 0.5, 2, 2, 0.5, 1, 1, 2, 1),
		array(1, 2, 1, 1, 1, 0.5, 0.5, 1, 1, 0.5, 2, 2, 0.5, 1, 0, 2, 2, 0.5),
		array(0.5, 1, 2, 1, 0.5, 1, 1, 1, 2, 1, 2, 1, 1, 0.5, 1, 1, 1, 1),
		array(2, 1, 1, 2, 1, 1, 1, 0.5, 1, 1, 0.5, 1, 2, 1, 1, 1, 0.5, 1),
		array(2, 1, 1, 1, 1, 1, 0.5, 1, 0.5, 1, 0.5, 1, 1, 1, 0.5, 1, 0, 2),
		array(0.5, 1, 1, 1, 2, 2, 1, 0.5, 0, 1, 1, 1, 1, 0.5, 1, 1, 1, 1),
		array(0.5, 1, 2, 1, 1, 0, 2, 2, 1, 1, 2, 1, 0.5, 1, 1, 1, 2, 1),
		array(1, 1, 1, 2, 1, 1, 2, 1, 1, 0.5, 1, 1, 1, 1, 1, 0, 0.5, 1),
		array(1, 1, 2, 0.5, 1, 2, 1, 1, 0.5, 1, 1, 2, 2, 1, 1, 1, 0.5, 1),
		array(2, 1, 0.5, 1, 0.5, 2, 1, 1, 2, 1, 1, 0.5, 1, 2, 1, 1, 0.5, 1),
		array(2, 1, 0.5, 0.5, 1, 0.5, 0.5, 1, 1, 2, 1, 1, 1, 1, 0.5, 2, 0.5, 0.5),
		array(1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 1, 1, 0.5, 0),
		array(1, 0, 1, 1, 1, 1, 1, 1, 1, 2, 1, 1, 1, 1, 2, 0.5, 0.5, 1),
		array(1, 1, 1, 0.5, 1, 1, 1, 1, 1, 2, 1, 1, 1, 1, 2, 0.5, 0.5, 0.5),
		array(1, 1, 0.5, 1, 0.5, 1, 1, 0.5, 1, 1, 2, 2, 1, 1, 1, 1, 0.5, 2),
		array(1, 1, 0.5, 2, 1, 1, 0.5, 1, 1, 1, 1, 1, 1, 2, 1, 2, 0.5, 1)
	);

	public function icon() {
		return "http://cdn.acceptableice.com/pkmn/type-badges/".$this->name.".png";
	}

	public function getOffensiveEffectiveness() {
		$out = array("SE" => array(), "NVE" => array(), "Immune" => array());
		if($this->id == 0) return $out;;
		$row = Type::$typeChart[$this->id - 1];
		for($i = 0; $i < count($row); $i++) {
			if($row[$i] == 2) $out["SE"][$i + 1] = 2;
			if($row[$i] == 0.5) $out["NVE"][$i + 1] = .5;
			if($row[$i] == 0) $out["Immune"][$i + 1] = 0;
		}
		return $out;
	}

	public function getOffensiveEffectivenessStrings() {
		$in = $this->getOffensiveEffectivness();
		$out = array("SE" => array(), "NVE" => array(), "Immune" => array());
		foreach($in as $k => $v) {
			foreach($v as $ik => $iv) {
				$out[$k][Type::find($ik)->name] = $iv;
			}
		}
		return $out;
	}

	public static function getCombinedOffensiveEffectiveness($type1, $type2) {
		$out = array("SE" => array(), "NVE" => array(), "Immune" => array());		
		$e1 = $type1->getOffensiveEffectiveness();
		$e2 = $type2->getOffensiveEffectiveness();
		foreach($e1 as $k => $v) {
			foreach($v as $ik => $iv) {
				$out[$k][$ik] = !array_key_exists($ik, $out[$k]) ? $iv : $out[$k][$ik] * $iv;
			}
		}

		foreach($e2 as $k => $v) {
			foreach($v as $ik => $iv) {
				$out[$k][$ik] = !array_key_exists($ik, $out[$k]) ? $iv : $out[$k][$ik] * $iv;
			}
		}

		foreach($out["SE"] as $k => $v) {
			if(array_key_exists($k, $out["NVE"])) {
				unset($out["NVE"][$k]);
				unset($out["SE"][$k]);
			}
		}

		foreach($out["Immune"] as $k => $v) {
			if(array_key_exists($k, $out["SE"])) unset($out["SE"][$k]);
			if(array_key_exists($k, $out["NVE"])) unset($out["NVE"][$k]);
		}

		return $out;
	}

	public static function getCombinedOffensiveEffectivenessStrings($type1, $type2) {
		$in = Type::getCombinedOffensiveEffectiveness($type1, $type2);
		$out = array("SE" => array(), "NVE" => array(), "Immune" => array());
		foreach($in as $k => $v) {
			foreach($v as $ik => $iv) {
				$out[$k][Type::find($ik)->name] = $iv;
			}
		}
		return $out;		
	}

	public function getDefensiveEffectiveness() {
		$out = array("SE" => array(), "NVE" => array(), "Immune" => array());
		if($this->id == 0) return $out;;
		for($i = 0; $i < count(Type::$typeChart); $i++) {
			if(Type::$typeChart[$i][$this->id - 1] == 2) $out["SE"][$i + 1] = 2;
			if(Type::$typeChart[$i][$this->id - 1] == 0.5) $out["NVE"][$i + 1] = .5;
			if(Type::$typeChart[$i][$this->id - 1] == 0) $out["Immune"][$i + 1] = 0;
		}
		return $out;
	}

	public function getDefensiveEffectivenessStrings() {
		$in = $this->getDefensiveEffectiveness();
		$out = array("SE" => array(), "NVE" => array(), "Immune" => array());
		foreach($in as $k => $v) {
			foreach($v as $ik => $iv) {
				$out[$k][Type::find($ik)->name] = $iv;
			}
		}
		return $out;
	}

	public static function getCombinedDefensiveEffectiveness($type1, $type2) {
		$out = array("SE" => array(), "NVE" => array(), "Immune" => array());		
		$e1 = $type1->getDefensiveEffectiveness();
		$e2 = $type2->getDefensiveEffectiveness();
		foreach($e1 as $k => $v) {
			foreach($v as $ik => $iv) {
				$out[$k][$ik] = !array_key_exists($ik, $out[$k]) ? $iv : $out[$k][$ik] * $iv;
			}
		}

		foreach($e2 as $k => $v) {
			foreach($v as $ik => $iv) {
				$out[$k][$ik] = !array_key_exists($ik, $out[$k]) ? $iv : $out[$k][$ik] * $iv;
			}
		}

		foreach($out["SE"] as $k => $v) {
			if(array_key_exists($k, $out["NVE"])) {
				unset($out["NVE"][$k]);
				unset($out["SE"][$k]);
			}
		}

		foreach($out["Immune"] as $k => $v) {
			if(array_key_exists($k, $out["SE"])) unset($out["SE"][$k]);
			if(array_key_exists($k, $out["NVE"])) unset($out["NVE"][$k]);
		}

		return $out;
	}

	public static function getCombinedDefensiveEffectivenessStrings($type1, $type2) {
		$in = Type::getCombinedDefensiveEffectiveness($type1, $type2);
		$out = array("SE" => array(), "NVE" => array(), "Immune" => array());
		foreach($in as $k => $v) {
			foreach($v as $ik => $iv) {
				$out[$k][Type::find($ik)->name] = $iv;
			}
		}
		return $out;		
	}

	public static function getFractionSigns($value) {
		return str_replace("0.25", "¼", str_replace("0.5", "½", "".$value));
	}
}

?>