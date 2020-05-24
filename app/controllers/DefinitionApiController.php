<?php
class DefinitionApiController extends BaseController {
  public function getTypes() {
    return Response::json(Type::all()->map(function ($type) {
      return [
        'id' => $type->id,
        'name' => $type->name
      ];
    }));
  }

  public function getTrainerOptions($campaignId) {
    $query = strtolower(Input::get('query'));

    return Response::json(Trainer::where('campaign_id', $campaignId)->get()->filter(function($trainer) use ($query) {
      return strpos(strtolower($trainer->name), $query) !== false;
    })->map(function ($type) {
      return [
        'value' => $type->id,
        'label' => $type->name
      ];
    }));
  }

  
  public function getAllMoves() {
    return MoveDefinition::all()->map(function ($definition) {
      return [
        'id' => $definition->id,
        'name' => $definition->name,
        'type' => $definition->type,
        'frequency' => $definition->frequency,
        'range' => $definition->attack_range,
        'damage' => $definition->damage,
        'accuracy' => $definition->ac,
        'attackType' => $definition->attack_type,
        'effects' => $definition->effects
      ];
    });
  }
  
  public function getAllSpecies() {
    return Species::all()->map(function ($definition) {
      return [
        'id' => $definition->id,
        'name' => $definition->name,
        'sprite' => $definition->sprite(),
        'animatedSprite' => $definition->animatedSprite()
      ];
    });
  }

  public function getSpeciesOptions() {
    $query = strtolower(Input::get('query'));

    return Response::json(Species::All()->filter(function($species) use ($query) {
      return strpos(strtolower($species->name), $query) !== false;
    })->map(function($species) {
      return [
        'value' => $species->id,
        'label' => $species->name
      ];
    }));
  }

  public function getNatureOptions() {
    $query = strtolower(Input::get('query'));

    return Response::json(Nature::All()->filter(function($nature) use ($query) {
      return strpos(strtolower($nature->name), $query) !== false;
    })->map(function($nature) {
      return [
        'value' => $nature->id,
        'label' => $nature->name
      ];
    }));
  }

  public function getAbilityOptions() {
    $query = strtolower(Input::get('query'));

    return Response::json(AbilityDefinition::All()->filter(function($ability) use ($query) {
      return strpos(strtolower($ability->name), $query) !== false;
    })->map(function($ability) {
      return [
        'value' => $ability->id,
        'label' => $ability->name
      ];
    }));
  }

  public function getHeldItemOptions() {
    $query = strtolower(Input::get('query'));
    
    return Response::json(HeldItem::All()->filter(function($heldItem) use ($query) {
      return strpos(strtolower($heldItem->name), $query) !== false;
    })->map(function($heldItem) {
      return [
        'value' => $heldItem->id,
        'label' => $heldItem->name
      ];
    }));
  }

  public function getCapabilityOptions() {
    $query = strtolower(Input::get('query'));
    
    return Response::json(CapabilityDefinition::All()->filter(function($capability) use ($query) {
      return strpos(strtolower($capability->name), $query) !== false;
    })->map(function($capability) {
      return [
        'value' => $capability->id,
        'label' => $capability->name
      ];
    }));
  }

  
  public function getMoveOptions() {
    $query = strtolower(Input::get('query'));
    
    return Response::json(MoveDefinition::All()->filter(function($move) use ($query) {
      return strpos(strtolower($move->name), $query) !== false;
    })->map(function($move) {
      return [
        'value' => $move->id,
        'label' => $move->name
      ];
    }));
  }

	public function getMove($id, $moveId) {
		if(is_null($moveId)) return Response::json(MoveDefinition::all());
		$move = MoveDefinition::find($moveId);
    if(is_null($move)) return Response::json('Move not found', 404);
    $pokemon = Pokemon::where('id', $id)->first();
		$out = array();
    $out['base'] = $move->toArray();
    
    $typeOverride = $pokemon->moves()->where('move', $moveId)->first()->type_override;
    $moveType = $typeOverride == 0 ? $move->type : $typeOverride;

    $typeData = Type::where('id', $moveType)->first();
    
    $out['base']['type'] = [
      'id' => $typeData->id,
      'name' => $typeData->name
    ];

		$ptu = $move->PTUDefinition()->get();
		$out['ptu']= $ptu->toArray();
		return Response::json($out);
	}

	public function getContestMove($id = null) {
		if(is_null($id)) return Response::json(MoveDefinition::all());
		$move = MoveDefinition::find($id);
		if(is_null($move)) return Response::json('Move not found', 404);
		return Response::json(array("id" => $move->id, "name" => $move->name, "contest_type" => $move->contest_type, "contest_dice" => $move->contest_dice, "contest_ability_name" => $move->contestEffect()->name,
									"contest_ability_desc" => $move->contestEffect()->description));
	}


	public function getCapability($id = null) {
		if(is_null($id)) return Response::json(CapabilityDefinition::all());
		$move = CapabilityDefinition::find($id);
		if(is_null($move)) return Response::json('Capability not found', 404);
		return Response::json($move);
	}

	public function getAbility($id = null) {
		if(is_null($id)) return Response::json(AbilityDefinition::all());
		$move = AbilityDefinition::find($id);
		if(is_null($move)) return Response::json('Ability not found', 404);
		$out = array();
		$out['base'] = $move->toArray();
		$out['ptu'] = $move->PTUDefinition()->get()->toArray();
		return Response::json($out);
	}

	public function getHeldItem($id = null) {
		if(is_null($id)) return Response::json(HeldItem::all());
		$move = HeldItem::find($id);
		if(is_null($move)) return Response::json('Held Item not found', 404);
		return Response::json($move);
	}
}


?>