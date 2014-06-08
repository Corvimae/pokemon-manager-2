<?php
class DefinitionApiController extends BaseController {

	public function getMove($id = null) {
		if(is_null($id)) return Response::json(MoveDefinition::all());
		$move = MoveDefinition::find($id);
		if(is_null($move)) return Response::json('Move not found', 404);
		$out = array();
		$out['base'] = $move->toArray();
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