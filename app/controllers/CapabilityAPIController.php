<?php

class CapabilityAPIController extends BaseController {
  public function setCapabilityOrder($id, $capabilityId, $position) {
    if(!PokemonUtils::validateOwnership($id)) return Response::json("Ownership mismatch");

    $pkmn = Pokemon::find($id);

    $capability = Capability::where('id', $capabilityId)->firstOrFail();

		$startPosition = $capability->position;

		if($position < $startPosition) {
			foreach($pkmn->capabilities()->where('position', '>=', $position)->where('position', '<', $startPosition)->get() as $c) {
				$c->position = $c->position + 1;
				$c->timestamps = false;
				$c->save();
			}
		} else if ($position > $startPosition) {
			foreach($pkmn->capabilities()->where('position', '>', $startPosition)->where('position', '<=', $position)->get() as $c) {
				$c->position = $c->position - 1;
				$c->timestamps = false;
				$c->save();
			}
    }
    
		$capability->position = $position;
		$capability->timestamps = false;
    $capability->save();
    
		return Response::json("Successfully updated capability order.");
  }
}

?>