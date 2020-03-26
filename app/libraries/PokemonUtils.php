<?php
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

  public static function validatePokemon($user, $pkmn) {
    if (is_null($user) || is_null($pkmn)) return false;

		return ($pkmn->user_id == $user->id || $user->isSpecificGM($pkmn->campaign()->id ?? -1));
  }
  
  public static function validateOwnership($id) {
    $user = Auth::user();
    $pkmn = Pokemon::find($id);
    
    return PokemonUtils::validatePokemon($user, $pkmn);
  }
  
	public static function validateTrainer($user, $trainer) {
		return ($trainer->user_id == $user->id || $user->isSpecificGM($trainer->campaign()->id));
  }
  
  public static function validateGM($pokemonId) {
    $user = Auth::user();
    $pkmn = Pokemon::find($pokemonId);

    return $user->isAdministrator() || $user->isSpecificGM($pkmn->campaign()->id ?? -1);
  }
}

?>