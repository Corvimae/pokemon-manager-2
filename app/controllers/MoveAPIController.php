<?php

class MoveAPIController extends BaseController {
	public function addMove($id, $moveId) {
    if(!PokemonUtils::validateOwnership($id)) return Response::json("Ownership mismatch");
    
    $definition = MoveDefinition::where('id', $moveId)->firstOrFail();
    
		$max = Move::where('pokemon_id', $id)->max('position');
		$move = new Move;
		$move->move = $definition->id;
		$move->pokemon_id = $id;
		$move->position = $max + 1;
		$move->is_tutor = false;
		$move->timestamps = false;
    $move->save();
    
    return Response::json([
      'id' => $move->id,
      'definition' => [
        'id' => $definition->id,
        'name' => $definition->name,
        'frequency' => $definition->frequency,
        'damage' => $definition->damage,
        'accuracy' => $definition->ac,
        'attackType' => $definition->attack_type
      ],
      'type' => [
        'id' => $move->type()->id,
        'name' => $move->type()->name,
      ],
      'ppUp' => $move->ppup ?? false,
      'isTutor' => $move->is_tutor
    ]);
  }
  
	public function deleteMove($id, $moveInstanceId) {
    if(!PokemonUtils::validateOwnership($id)) return Response::json("Ownership mismatch");

    $pkmn = Pokemon::find($id);

    $move = Move::where('id', $moveInstanceId)->first();
    $position = $move->position;
    $move->delete();

		foreach($pkmn->moves()->where('position', '>', $position)->get() as $subsequentMove) {
			$subsequentMove->position = $subsequentMove->position - 1;
			$subsequentMove->timestamps = false;
			$subsequentMove->save();
    }
    
		return Response::json("Succesfully deleted move.");	
  }
  
  public function setPPUp($id, $moveInstanceId, $value) {
    if(!PokemonUtils::validateOwnership($id)) return Response::json("Ownership mismatch");

    $move = Move::where('id', $moveInstanceId)->first();
    $move->ppup = $value;
    $move->timestamps = false;
    $move->save();

    return Response::json("Succesfully set PP Up status.");	
  }

	public function setMoveType($id, $moveInstanceId, $typeId) {
    if(!PokemonUtils::validateOwnership($id)) return Response::json("Ownership mismatch");

    $move = Move::where('id', $moveInstanceId)->first();
    $move->type_override = $typeId;
    $move->timestamps = false;
    $move->save();

		return Response::json("Move type overridden");
  }
  
  public function setMoveOrder($id, $moveId, $position) {
    if(!PokemonUtils::validateOwnership($id)) return Response::json("Ownership mismatch");

    $pkmn = Pokemon::find($id);

    $move = Move::where('id', $moveId)->firstOrFail();

		$startPosition = $move->position;

		if($position < $startPosition) {
			foreach($pkmn->moves()->where('position', '>=', $position)->where('position', '<', $startPosition)->get() as $m) {
				$m->position = $m->position + 1;
				$m->timestamps = false;
				$m->save();
			}
		} else if ($position > $startPosition) {
			foreach($pkmn->moves()->where('position', '>', $startPosition)->where('position', '<=', $position)->get() as $m) {
				$m->position = $m->position - 1;
				$m->timestamps = false;
				$m->save();
			}
    }
    
		$move->position = $position;
		$move->timestamps = false;
    $move->save();
    
		return Response::json("Successfully updated move order.");
  }
}

?>