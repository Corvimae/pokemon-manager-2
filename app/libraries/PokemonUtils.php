<?
class PokemonUtils {
	public static function convertIntToStat($in) {
		switch($in) {
			case 0: return "HP";
			case 1: return "Attack";
			case 2: return "Defense";
			case 3: return "Special Attack";
			case 4: return "Special Defense";
			case 5: return "Speed";
			default: return "Undefined";
		}
	}

}

?>