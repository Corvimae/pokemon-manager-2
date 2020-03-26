<?php
  class PokemonAPIController extends BaseController {
    public function setType($id, $position, $typeId) {
      $pkmn = Pokemon::find($id);

      if(!PokemonUtils::validateOwnership($id)) return Response::json("Ownership mismatch");

      if($position == 1) $pkmn->type1 = $typeId;
      if($position == 2) $pkmn->type2 = $typeId;
      
      $pkmn->save();
      
      return Response::json("Type update successful");
    }

    public function setActive($id, $active) {
      $pkmn = Pokemon::find($id);

      if(!PokemonUtils::validateOwnership($id)) return Response::json("Ownership mismatch");

      $pkmn->active = $active;
      
      $pkmn->save();
      
      return Response::json("Active status successfully set.");
    }
   
    public function setName($id, $value) {
      if(!PokemonUtils::validateOwnership($id)) return Response::json("Ownership mismatch");
  
      $pokemon = Pokemon::where('id', $id)->firstOrFail();
      $pokemon->name = $value;
      $pokemon->timestamps = false;
      $pokemon->save();
  
      return Response::json("Successfully updated Pokémon name.");
    }
   
    public function setOwner($id, $value) {
      if(!PokemonUtils::validateGM($id)) return Response::json("Must be a GM of this Pokémon's campaign.");
      
      $user = Auth::user();

      $trainer = Trainer::where('id', $value)->firstOrFail();
      $pokemon = Pokemon::where('id', $id)->firstOrFail();
      $pokemon->user_id = $trainer->user()->id;
      $pokemon->trainer_id = $trainer->id;
      $pokemon->save();
  
      MessageController::sendMessage(
        $pokemon->user_id,
        $user->id, 
        'You have been given control of '.$pokemon->name, 'You have been given full edit rights to '.
        $pokemon->name.', a level '.$pokemon->level().' '.$pokemon->species()->name.
        '. They are currently assigned to the trainer '.$trainer->name.'.<br><br>'.
        'You can view their page <a href="/pokemon/'.$pokemon->id.'"> here</a>.'
      );

      return Response::json("Successfully updated Pokémon owner.");
    }
       
    public function setExperience($id, $experience) {
      if(!PokemonUtils::validateOwnership($id)) return Response::json("Ownership mismatch");
  
      $pokemon = Pokemon::where('id', $id)->firstOrFail();
      $pokemon->experience = $experience;
      $pokemon->timestamps = false;
      $pokemon->save();
  
      return Response::json("Successfully updated Pokémon experience.");
    }

    public function setSpecies($id, $speciesId) {
      if(!PokemonUtils::validateOwnership($id)) return Response::json("Ownership mismatch");
  
      $pokemon = Pokemon::where('id', $id)->firstOrFail();
      $species = Species::where('id', $speciesId)->firstOrFail();

      $pokemon->species = $species->id;
      $pokemon->timestamps = false;
      $pokemon->save();
  
      return Response::json([
				'id' => $species->id,
				'name' => $species->name,
				'spriteURL' => $species->animatedSprite()
      ]);
    }

    public function setGender($id, $value) {
      if(!PokemonUtils::validateOwnership($id)) return Response::json("Ownership mismatch");
  
      $pokemon = Pokemon::where('id', $id)->firstOrFail();

      $genderValue = $value == "Male" ? 0 : ($value == "Female" ? 1 : 2);

      $pokemon->gender = $genderValue;
      $pokemon->timestamps = false;
      $pokemon->save();
  
      return Response::json("Successfully updated Pokémon gender.");
    }

    public function setLoyalty($id, $value) {
      if(!PokemonUtils::validateOwnership($id)) return Response::json("Ownership mismatch");
  
      $pokemon = Pokemon::where('id', $id)->firstOrFail();

      $pokemon->loyalty = $value;
      $pokemon->timestamps = false;
      $pokemon->save();
  
      return Response::json("Successfully updated Pokémon loyalty.");
    }

    public function updateBaseStat($id, $stat, $value) {
      if(!PokemonUtils::validateOwnership($id)) return Response::json("Ownership mismatch");
  
      $pokemon = Pokemon::where('id', $id)->firstOrFail();
      $baseStats = $pokemon->baseStats();
      $baseStats->$stat = $value;
      $baseStats->timestamps = false;
      $baseStats->save();

      return Response::json("Successfully updated base stats.");
    }

    public function updateAddedStat($id, $stat, $value) {
      if(!PokemonUtils::validateOwnership($id)) return Response::json("Ownership mismatch");
  
      $pokemon = Pokemon::where('id', $id)->firstOrFail();
      $addedStats = $pokemon->addStats();
      $addedStats->$stat = $value;
      $addedStats->timestamps = false;
      $addedStats->save();

      return Response::json("Successfully updated added stats.");
    }

    public function updateNotes($id) {
      if(!PokemonUtils::validateOwnership($id)) return Response::json("Ownership mismatch");
  
      $pokemon = Pokemon::where('id', $id)->firstOrFail();

      $pokemon->notes = Input::get('notes');
      $pokemon->timestamps = false;
      $pokemon->save();

      return Response::json("Successfully updated Pokémon notes.");
    }

    public function updateGMNotes($id) {
      if(!PokemonUtils::validateGM($id)) return Response::json("Must be a GM of this Pokémon's campaign.");
  
      $pokemon = Pokemon::where('id', $id)->firstOrFail();

      $pokemon->gm_notes = Input::get('notes');
      $pokemon->timestamps = false;
      $pokemon->save();

      return Response::json("Successfully updated Pokémon GM notes.");
    }
  }
?>