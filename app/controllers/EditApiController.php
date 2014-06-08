<?php
class EditApiController extends BaseController {

	public static function validatePokemon($user, $pkmn) {
		return ($pkmn->user_id == $user->id || $user->isSpecificGM($pkmn->legacy));
	}

	public static function validateTrainer($user, $trainer) {
		return ($trainer->user_id == $user->id || $user->isSpecificGM($trainer->primaryCampaign()));
	}

	public function testFunction() {
		echo '<pre>';print_r((object) HomeController::getAllActiveUsers()); echo '</pre>';
	}

	public function addTrainerClass($trainer, $class) {
		$user = Auth::user();
		$trainer = Trainer::find($trainer);
		if(!EditApiController::validateTrainer($user, $trainer)) return Response::json("Ownership mismatch");
		$tc = new TrainerClass;
		$tc->trainer_id = $trainer->id;
		$tc->class_id = $class;
		$tc->timestamps = false;
		$tc->save();
		return Response::json("Trainer class successfully added");
	}

	public function updateTrainerCampaign($trainer, $campaign) {
		$user = Auth::user();
		$trainer = Trainer::find($trainer);
		if(!EditApiController::validateTrainer($user, $trainer)) return Response::json("Ownership mismatch");
		$trainer->campaign_id = $campaign;
		$trainer->timestamps = false;
		$trainer->save();
		return Response::json("Trainer campaign successfully updated");
	}

	public function updateCampaignHealthFormula($campaign) {
		$user = Auth::user();
		$cmp = Campaign::find($campaign);
		if(!$user->isSpecificGM($cmp->id)) return Response::json("Ownership mismatch");
		$cmp->health_formula = Input::get('value');
		$cmp->timestamps = false;
		$cmp->save();
		return Response::json('Health formula update successful');
	}
	
	public function updateCampaignSetIsPTU($campaign) {
		$user = Auth::user();
		$cmp = Campaign::find($campaign);
		if(!$user->isSpecificGM($cmp->id)) return Response::json("Ownership mismatch");
		$cmp->isPTU = Input::get('value') == 'true' ? 1 : 0;
		$cmp->timestamps = false;
		$cmp->save();
		return Response::json('PTU setting update successful');		
	}
	public function updateHealth($id, $val) {
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$pkmn->current_health = $val;
		$pkmn->save();
		return Response::json("Health update successful");
	}

	public function updateName($id, $name) {
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$pkmn->name = $name;
		$pkmn->save();
		return Response::json("Name update successful");
	}

	public function updateExperience($id, $xp) {
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$pkmn->experience = $xp;
		$pkmn->save();
		return Response::json("Experience update successful");
	}

	public function setNotes($id) {
		echo $id;
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$pkmn->notes = Input::get('value');
		$pkmn->save();
		return Response::json("Notes update successful");
	}


	public function giveToTrainer($id, $trainer) {
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!$user->isSpecificGM($pkmn->trainer()->campaign()->id)) return Response::json("Ownership mismatch");
		$t_obj = Trainer::find($trainer);
		$pkmn->user_id = $t_obj->user()->id;
		$pkmn->trainer_id = $trainer;
		$pkmn->save();
		MessageController::sendMessage($pkmn->user_id, $user->id, 'You have been given control of '.$pkmn->name, 'You have been given full edit rights to '.$pkmn->name.', a level '.$pkmn->level().' '.$pkmn->species()->name.'. They are currently assigned to the trainer '.$t_obj->name.'.<br><br>'.
									   'You can view their page <a href="/pokemon/'.$pkmn->id.'"> here</a>.');
		return Response::json("Pokemon ownership move successful");
	}

	public function updateLoyalty($id, $val) {
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!$user->isSpecificGM($pkmn->legacy)) return Response::json("Ownership mismatch");
		$pkmn->loyalty = $val;
		$pkmn->save();
		return Response::json("Name update successful");
	}

	public function reorderMove($id, $val) {
		$user = Auth::user();
		$mv = Move::find($id);
		$pkmn = Pokemon::where("id", "=", $mv->pokemon_id)->firstOrFail();
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$start = $mv->position;

		if($val < $start) {
			foreach($pkmn->moves()->where('position', '>=', $val)->where('position', '<', $start)->get() as $m) {
				$m->position = $m->position + 1;
				$m->timestamps = false;
				$m->save();
			}

		} else if ($val > $start) {
			foreach($pkmn->moves()->where('position', '>', $start)->where('position', '<=', $val)->get() as $m) {
				$m->position = $m->position - 1;
				$m->timestamps = false;
				$m->save();
			}
		}
		$mv->position = $val;
		$mv->timestamps = false;
		$mv->save();
		return Response::json("Move reposition successful");
	}

	public function typeOverrideMove($id, $val) {
		$user = Auth::user();
		$mv = Move::find($id);
		$pkmn = Pokemon::where("id", "=", $mv->pokemon_id)->firstOrFail();
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$mv->typeOverride = $val;
		$mv->timestamps = false;
		$mv->save();
		return Response::json(array("icon" => $mv->type()->icon()));
	}

	public function sortTrainerPokemon($id, $order) {
		$user = Auth::user();
		$trainer = Trainer::find($id);
		if($id != 0 && $trainer->user_id != $user->id) return Response::json("Ownership mismatch");
		$i = 1;
        foreach (explode(",", $order) as $recordID) {
        	foreach(Pokemon::where('id', '=', $recordID)->get() as $pkmn) {
				if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
	          	$pkmn->position = $i;
	          	$pkmn->save();
	            $i++;
	        }
        }

		return Response::json("Pokemon reposition successful");
	}

	public function setPokemonTrainer($id, $trainer) {
		$user = Auth::user();
		$pkmn = Pokemon::where("id", "=", $id)->firstOrFail();
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		if($trainer != 0) {
			$trn = Trainer::where('id', $trainer)->firstOrFail();
			if($trn->user_id != $user->id) return Response::json("Ownership mismatch");
		}
		$pkmn->trainer_id = $trainer;
		$pkmn->timestamps = false;
		$pkmn->save();

		return Response::json("Pokemon trainer successfully set");
	}


	public function reorderCapability($id, $val) {
		$user = Auth::user();
		$mv = Capability::find($id);
		$pkmn = Pokemon::where("id", "=", $mv->pokemon_id)->firstOrFail();
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$start = $mv->position;

		if($val < $start) {
			foreach($pkmn->capabilities()->where('position', '>=', $val)->where('position', '<', $start)->get() as $m) {
				$m->position = $m->position + 1;
				$m->timestamps = false;
				$m->save();
			}

		} else if ($val > $start) {
			foreach($pkmn->capabilities()->where('position', '>', $start)->where('position', '<=', $val)->get() as $m) {
				$m->position = $m->position - 1;
				$m->timestamps = false;
				$m->save();
			}
		}
		$mv->position = $val;
		$mv->timestamps = false;
		$mv->save();
		return Response::json("Move reposition successful");
	}

	public function updateLegacy($id, $val) {
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$pkmn->legacy = $val;
		$pkmn->save();
		return Response::json("Legacy status update successful");
	}

	public function updateRuleset($id, $val) {
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$pkmn->ruleset = $val;
		$pkmn->save();
		return Response::json("Legacy status update successful");
	}

	public function updateHidden($id, $val) {
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!$user->isSpecificGM($pkmn->legacy)) return Response::json("Ownership mismatch");
		$pkmn->hidden = $val;
		$pkmn->save();
		return Response::json("Hidden update successful");
	}

	public function updateHeldItem($id, $val) {
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$item = HeldItem::where("name", "=", "$val")->firstOrFail();
		$pkmn->held_item = $item->id;
		$pkmn->save();
		return Response::json(array("id" => $item->id));
	}	

	public function updateGMNotes($id, $val) {
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$pkmn->gm_notes = $val;
		$pkmn->save();
		return Response::json("GM Notes update successful");
	}	

	public function updateActive($id, $val) {
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$pkmn->active = $val;
		$pkmn->save();
		return Response::json("Active status update successful");
	}

	public function updateType($id, $pos, $val) {
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		if($pos == 1) $pkmn->type1 = $val;
		if($pos == 2) $pkmn->type2 = $val;
		$pkmn->save();
		return Response::json("Type update successful");
	}

	public function updateSpecies($id, $val) {
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$species = Species::where("name", "=", $val)->firstOrFail();
		$pkmn->species = $species->id;
		$pkmn->save();
		return Response::json(array("id" => $species->id, "animation" => $species->animatedSprite()));
	}

	public function updateNature($id, $val) {
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$nature = Nature::where("name", "=", "$val")->firstOrFail();
		$pkmn->nature = $nature->id;
		$pkmn->save();
		return Response::json("Nature update successful");
	}

	public function removeAbility($id, $ability) {
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$abs = $pkmn->abilities()->where("ability", '=', $ability)->firstOrFail();
		$abs->delete();
		return Response::json("Deletion successful");
	}

	public function insertAbility($id, $val) {
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$abd = AbilityDefinition::where('name', '=', str_replace('-', ' ', $val))->firstOrFail();
		$abs = new Ability;
		$abs->ability = $abd->id;
		$abs->pokemon_id = $id;
		$abs->timestamps = false;
		$abs->save();
		return Response::json($abd->id);
	}

	public function insertMove($id, $val) {
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$abd = MoveDefinition::where('name', '=', str_replace('-', ' ', $val))->firstOrFail();
		$max = Move::where('pokemon_id', $id)->max('position');
		$abs = new Move;
		$abs->move = $abd->id;
		$abs->pokemon_id = $id;
		$abs->position = $max + 1;
		$abs->isTutor = false;
		$abs->timestamps = false;
		$abs->save();
		$type = Type::where('id', '=', $abd->type)->first();
		return Response::json(array("id" => $abd->id, "uniq_id" => $abs->id, "name" => $abd->name, "frequency" => $abd->frequency, "ptu_move_frequency" => (is_null($abd->PTUDefinition()->first()) ? '???' : $abd->PTUDefinition()->first()->frequency), "type" => $type->name, "contest_type" => $abd->contest_type, "contest_dice" => $abd->contest_dice, "contest_effect" => $abd->contestEffect()->name));
	}

	public function updateMoveTutorStatus($id, $tutor) {
		$user = Auth::user();
		$mv = Move::find($id);
		$pkmn = Pokemon::where("id", "=", $mv->pokemon_id)->firstOrFail();
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$mv->isTutor = $tutor;
		$mv->timestamps = false;
		$mv->save();
		return Response::json("Tutor status update successful");
	}
	
	public function updateMovePPUpStatus($id, $ppup) {
		$user = Auth::user();
		$mv = Move::find($id);
		$pkmn = Pokemon::where("id", "=", $mv->pokemon_id)->firstOrFail();
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$mv->ppUp = $ppup;
		$mv->timestamps = false;
		$mv->save();
		return Response::json("Tutor status update successful");
	}


	public function removeMove($id, $val) {
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$abs = $pkmn->moves()->where("move", '=', $val)->firstOrFail();
		$pos = $abs->position;
		$abs->delete();
		foreach($pkmn->moves()->where('position', '>', $pos)->get() as $m) {
			$m->position = $m->position - 1;
			$m->timestamps = false;
			$m->save();
		}
		return Response::json("Deletion successful");	
	}

	public function insertCapability($id, $cap, $val) {
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$abd = CapabilityDefinition::where('name', '=', str_replace('-', ' ', $cap))->firstOrFail();
		$max = Capability::where('pokemon_id', $id)->max('position');
		$abs = new Capability;
		$abs->capability = $abd->id;
		$abs->pokemon_id = $id;
		$abs->position = $max + 1;
		$abs->value = $val;
		$abs->timestamps = false;
		$abs->save();
		return Response::json(array("id" => $abd->id, "uniq_id" => $abs->id, "cap" => $abd->name, "val" => $val));
	}

	public function removeCapability($id, $val) {
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$abs = $pkmn->capabilities()->where("id", '=', $val)->firstOrFail();
		$pos = $abs->position;
		$abs->delete();
		foreach($pkmn->capabilities()->where('position', '>', $pos)->get() as $m) {
			$m->position = $m->position - 1;
			$m->timestamps = false;
			$m->save();
		}
		return Response::json("Deletion successful");	
	}

	public function deletePokemon($id) {
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$pkmn->delete();
		return Response::json("Deletion successful");	
	}

	public function updateStat($id, $stat, $val) {
		$user = Auth::user();
		$pkmn = Pokemon::find($id);
		if(!EditApiController::validatePokemon($user, $pkmn)) return Response::json("Ownership mismatch");
		$bst = $pkmn->baseStats();
		$bst->timestamps = false;

		$add = $pkmn->addStats();
		$add->timestamps = false;

		switch($stat) {
			case "base-health": $bst->hp = $val; break;
			case "base-attack": $bst->attack = $val; break;
			case "base-defense": $bst->defense = $val; break;
			case "base-spattack": $bst->spattack = $val; break;
			case "base-spdefense": $bst->spdefense = $val; break;
			case "base-speed": $bst->speed = $val; break;
			case "add-health": $add->hp = $val; break;
			case "add-attack": $add->attack = $val; break;
			case "add-defense": $add->defense = $val; break;
			case "add-spattack": $add->spattack = $val; break;
			case "add-spdefense": $add->spdefense = $val; break;
			case "add-speed": $add->speed = $val; break;

		}
		$add->save();
		$bst->save();
		return Response::json("Stat update successful");

	}

	public function addTrainer($name) {
		$user = Auth::user();
		$trainer = new Trainer;
		$trainer->user_id = $user->id;
		$trainer->name = $name;
		$trainer->timestamps = false;
		$trainer->save();
		return Response::json("Creation successful");	

	}



}


?>