<?php
class DataApiController extends BaseController {
	public function getPokemonInfo($id) {
		$user = Auth::user();
		if(is_null($id)) return Response::json(['error' => 'ID parameter must be specified.'], 403);

		$pokemon = Pokemon::find($id);
		if(is_null($pokemon)) return Response::json(['error' => 'Pokemon not found.'], 404);
		
		$isGM = $user ? $user->isSpecificGM($pokemon->campaign()->id) : false; 

		$species = $pokemon->species();
		$campaign = $pokemon->campaign();
		$baseStats = $pokemon->baseStats();
    $addedStats = $pokemon->addStats();
    $combatStages = $pokemon->combatStages();

		return Response::json([
      'id' => $pokemon->id,
      'name' => $pokemon->name,
      'isUserGM' => $isGM,
      'experience' => $pokemon->experience,
      'gender' => $pokemon->gender(),
      'currentHealth' => $pokemon->current_health,
			'nature' => [
				'id' => $pokemon->nature()->id,
				'name' => $pokemon->nature()->name
			],
			'owner' => [
				'id' => is_null($pokemon->trainer()) ? $pokemon->owner()->id : $pokemon->trainer()->id,
        'name' => is_null($pokemon->trainer()) ? $pokemon->owner()->username : $pokemon->trainer()->name,
        'classes' => is_null($pokemon->trainer()) ? [] : $pokemon->trainer()->classes()->get()->map(function($class) {
          return [
            'id' => $class->definition()->id,
            'name' => $class->definition()->name
          ];
        }),
			],
			'types' => [
				[
					'id' => $pokemon->type1()->id,
					'name' => $pokemon->type1()->name,
					'icon' => $pokemon->type1()->icon()
				],
				[
					'id' => $pokemon->type2()->id,
					'name' => $pokemon->type2()->name,
					'icon' => $pokemon->type2()->icon()
				],
			],
			'stats' => [
				'base' => [
					'hp' => $baseStats->hp,
					'attack' => $baseStats->attack,
					'defense' => $baseStats->defense,
					'spattack' => $baseStats->spattack,
					'spdefense' => $baseStats->spdefense,
					'speed' => $baseStats->speed
				],
				'added' => [
					'hp' => $addedStats->hp,
					'attack' => $addedStats->attack,
					'defense' => $addedStats->defense,
					'spattack' => $addedStats->spattack,
					'spdefense' => $addedStats->spdefense,
					'speed' => $addedStats->speed
        ],
        'combatStages' => [
          'attack' => $combatStages->attack,
          'defense' => $combatStages->defense,
          'spattack' => $combatStages->spattack,
          'spdefense' => $combatStages->spdefense,
          'speed' => $combatStages->speed
        ]
			],
			'species' => [
				'id' => $species->id,
				'name' => $species->name,
				'spriteURL' => $species->animatedSprite()
			],
			'heldItem' => [
				'id' => $pokemon->heldItem()->id,
				'name' => $pokemon->heldItem()->name
      ],
      'loyalty' => ($isGM || ($user ? $user->hasPermissionValue('Loyalty', $pokemon->campaign()->id) : false)) ? $pokemon->loyalty : null,
      'notes' => $pokemon->notes,
			'gmNotes' => $isGM ? str_replace(PHP_EOL, '\\n', $pokemon->gm_notes) : null,
			'moves' => $pokemon->moves()->get()->map(function($move) {
				$definition = $move->definition();

				return [
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
            'name' => $move->type()->name
          ],
          'ppUp' => $move->ppup ?? false,
					'isTutor' => $move->is_tutor
				];
			}),
			'abilities' => $pokemon->abilities()->get()->map(function($ability) {
				$definition = $ability->definition();

				return [
					'id' => $ability->id,
					'definition' => [
						'id' => $definition->id,
						'name' => $definition->name
					]
				];
			}),
			'capabilities' => $pokemon->capabilities()->get()->map(function($capability) {
				$definition = $capability->definition();

				return [
					'id' => $capability->id,
					'definition' => [
						'id' => $definition->id,
						'name' => $definition->name,
					],
					'value' => $capability->value,
				];
			}),
			'defenses' => $pokemon->getCombinedDefensiveTypeEffectivenessStrings(),
			'isActive' => $pokemon->active ?? false,
			'isHidden' => $pokemon->isHidden ?? false,
			'campaign' => [
				'id' => $campaign->id,
				'name' => $campaign->name,
				'healthFormula' => $campaign->health_formula,
				'physicalEvasionFormula' => $campaign->physical_evasion_formula,
				'specialEvasionFormula' => $campaign->special_evasion_formula,
				'speedEvasionFormula' => $campaign->speed_evasion_formula
      ],
		]);
  }
  
  public function getOtherTrainerPokemon($pokemonId) {
    $user = Auth::user();
		if(is_null($pokemonId)) return Response::json(['error' => 'ID parameter must be specified.'], 403);

		$pokemon = Pokemon::find($pokemonId);
    if(is_null($pokemon)) return Response::json(['error' => 'Pokemon not found.'], 404);
    
    $isGM = $user ? $user->isSpecificGM($pokemon->campaign()->id) : false; 

    $query = Pokemon::where('trainer_id', $pokemon->trainer()->id)->where('id', '!=', $pokemonId);

    if (!$isGM) $query = $query->where('active', true);
    return Response::json(
        $query
        ->orderBy('position')
        ->get()
        ->map(function ($otherPokemon) {
          return [
            'id' => $otherPokemon->id,
            'name' => $otherPokemon->name,
            'icon' => $otherPokemon->species()->sprite(),
            'experience' => $otherPokemon->experience,
            'species' => $otherPokemon->species()->name
          ];
        })
    );
  }
}
?>