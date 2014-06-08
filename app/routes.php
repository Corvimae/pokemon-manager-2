<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function() {
	if(!Auth::check()) return Redirect::to('login');
	$user = Auth::user();
	return View::make('main', array('user' => $user));
});

Route::get('/migrate/{val}', 'HomeController@migrateMoves');

Route::get('/pokemon/new', 'HomeController@createNewPokemon');

Route::get('/trainers/{game}', function($game) {
	return View::make('trainers')->with('game', $game);
});;

Route::get('/test', 'EditApiController@testFunction');


Route::get('/messages/unread/{page?}', function($page = 1) {
	return View::make('messages')->with('page', $page)->with('showUnread', true);
});

Route::get('/messages/{page?}', function($page = 1) {
	return View::make('messages')->with('page', $page)->with('showUnread', false);
});

Route::get('/gmpanel/{campaign}', function($campaign){ 
	$user = Auth::user();
	if(!$user->isSpecificGM($campaign)) return Redirect::to('/');
	return View::make('gmpanel')->with('campaign', Campaign::find($campaign));
});

Route::get('/user/{id}/', function($id) {
	$user = User::find($id);
	if(is_null($user)) return Redirect::to('/');
	return View::make('user')->with('user', $user);
});

Route::get('/trainer/{id}/', function($id) {
	$user = Auth::user();
	$trainer = Trainer::find($id);
	if(is_null($trainer) || !($trainer->user()->id == $user->id || $user->isSpecificGM($trainer->campaign()->id) || $user->isAdministrator())) return Redirect::to('/');
	return View::make('trainer')->with('trainer', $trainer);
});

Route::get('/pokemon/{id}/', function($id) {
	$user = Auth::user();
	$pkmn = Pokemon::find($id);
	if(is_null($pkmn) || ($pkmn->owner()->isGM() && !$user->isGM())) return Redirect::to('/');
	if($pkmn->hidden && !$user->isSpecificGM($pkmn->legacy)) return View::make('hidden')->with('pkmn', $pkmn);
	return View::make('pokemon')->with('pkmn', $pkmn)->with('shouldEdit', 0);
});

Route::get('/pokemon/test/{id}/', function($id) {
	$user = Auth::user();
	$pkmn = Pokemon::find($id);
	if(is_null($pkmn)) return Redirect::to('/');
	if(!$user->isGM()) return Redirect::to('/');
	return View::make('test')->with('pkmn', $pkmn)->with('shouldEdit', 0);
});

Route::get('/pokemon/dnd/{id}/', function($id) {
	$user = Auth::user();
	$pkmn = Pokemon::find($id);
	if(is_null($pkmn)) return Redirect::to('/');
	if($pkmn->hidden && !$user->isSpecificGM($pkmn->legacy)) return View::make('hidden')->with('pkmn', $pkmn);
	return View::make('dnd')->with('pkmn', $pkmn)->with('shouldEdit', 0);
});

Route::get('logout', array('uses' => 'HomeController@doLogout'));

Route::get('login', array('uses' => 'HomeController@showLogin'));
Route::post('login', array('uses' => 'HomeController@doLogin'));
Route::post('createAccount', array('uses' => 'HomeController@createAccount'));

Route::group(array('prefix' => 'api/v1',), function() {

	Route::get('/campaign/search', 'HomeController@searchCampaigns');
	Route::post('/campaign/{campaign}/formula/health/update', 'EditApiController@updateCampaignHealthFormula');
	Route::post('/campaign/{campaign}/setting/ptu/update', 'EditApiController@updateCampaignSetIsPTU');

	Route::any('/messages/seen/{id}', 'MessageController@markMessageAsRead');
	Route::any('/moves/{id?}/', 'DefinitionApiController@getMove');
	Route::any('/contest/moves/{id?}/', 'DefinitionApiController@getContestMove');

	Route::any('/capabilities/{id?}/', 'DefinitionApiController@getCapability');
	Route::any('/abilities/{id?}/', 'DefinitionApiController@getAbility');
	Route::any('/helditems/{id?}/', 'DefinitionApiController@getHeldItem');

	Route::any('/pokemon/{id}/update/health/{val}', 'EditApiController@updateHealth');
	Route::any('/pokemon/{id}/update/name/{val}', 'EditApiController@updateName');
	Route::any('/pokemon/{id}/update/legacy/{val}', 'EditApiController@updateLegacy');
	Route::any('/pokemon/{id}/update/ruleset/{val}', 'EditApiController@updateRuleset');
	Route::any('/pokemon/{id}/update/hidden/{val}', 'EditApiController@updateHidden');
	Route::any('/pokemon/{id}/update/active/{val}', 'EditApiController@updateActive');
	Route::any('/pokemon/{id}/update/type/{pos}/{val}', 'EditApiController@updateType');
	Route::any('/pokemon/{id}/update/nature/{val}', 'EditApiController@updateNature');
	Route::any('/pokemon/{id}/update/stat/{stat}/{val}', 'EditApiController@updateStat');
	Route::any('/pokemon/{id}/update/species/{val}', 'EditApiController@updateSpecies');
	Route::any('/pokemon/{id}/update/xp/{val}', 'EditApiController@updateExperience');
	Route::any('/pokemon/{id}/update/loyalty/{val}', 'EditApiController@updateLoyalty');
	Route::any('/pokemon/{id}/update/trainer/{trainer}', 'EditApiController@setPokemonTrainer');
	Route::any('/pokemon/{id}/update/helditem/{val}', 'EditApiController@updateHeldItem');
	Route::any('/pokemon/{id}/update/gmnotes/{val}', 'EditApiController@updateGMNotes');
	Route::any('/pokemon/{id}/insert/ability/{val}', 'EditApiController@insertAbility');
	Route::any('/pokemon/{id}/remove/ability/{val}', 'EditApiController@removeAbility');
	Route::any('/pokemon/{id}/insert/move/{val}', 'EditApiController@insertMove');
	Route::any('/pokemon/{id}/give/{val}', 'EditApiController@giveToTrainer');
	Route::any('/pokemon/{id}/remove/move/{val}', 'EditApiController@removeMove');
	Route::any('/pokemon/{id}/delete', 'EditApiController@deletePokemon');
	Route::any('/pokemon/{id}/insert/capability/{cap}/{val}', 'EditApiController@insertCapability');
	Route::any('/pokemon/{id}/remove/capability/{val}', 'EditApiController@removeCapability');

	Route::any('/move/{id}/update/tutor/{tutor}', 'EditApiController@updateMoveTutorStatus');
	Route::any('/move/{id}/update/ppup/{ppup}', 'EditApiController@updateMovePPUpStatus');

	Route::any('/move/{id}/update/reorder/{val}', 'EditApiController@reorderMove');
	Route::any('/move/{id}/update/type/{val}', 'EditApiController@typeOverrideMove');


	Route::any('/capability/{id}/update/reorder/{val}', 'EditApiController@reorderCapability');

	Route::any('/trainer/{id}/pokemon/sort/{order}', 'EditApiController@sortTrainerPokemon');
	Route::any('/trainer/add/{name}', 'EditApiController@addTrainer');
	Route::any('/trainer/{trainer}/class/add/{class}', 'EditApiController@addTrainerClass');
	Route::any('/trainer/{trainer}/campaign/update/{campaign}', 'EditApiController@updateTrainerCampaign');
	
	Route::post('/gm/motd', 'HomeController@setMOTD');
	Route::post('/pokemon/{id}/update/notes', 'EditApiController@setNotes');

});

