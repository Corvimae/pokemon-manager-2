<?php

class HomeController extends BaseController {

	public function createNewPokemon() {
		$user = Auth::user();

		$pkmn = new Pokemon;

		$pkmn->name = "New Pokemon";
		$pkmn->user_id = $user->id;
		$pkmn->species = 1;
		$pkmn->experience = 0;
		$pkmn->nature = 1;
		$pkmn->gender = 1;
		$pkmn->type1 = 1;
		$pkmn->type2 = 1;
		$pkmn->notes = "Nothing to say, apparently.";
		$pkmn->current_health = 0;
		$pkmn->position = Pokemon::where('user_id', $user->id)->max('position');
		if(!isset($pkmn->position)) $pkmn->position = 0;
		$pkmn->save();

		$base = new StatList;
		$base->pokemon_id = $pkmn->id;
		$base->isBase = 1;
		$base->timestamps = false;
		$base->save();

		$add = new StatList;
		$add->pokemon_id = $pkmn->id;
		$add->timestamps = false;
		$add->save();

		return Redirect::to('/pokemon/'.$pkmn->id);
	}

	public function migrateMoves($val) {
		$user = User::where('username', $val)->firstOrFail();
		echo 'Pokemon movesets for '.$user->username.'<br><br>';
		foreach(Pokemon::where('user_id', $user->id)->get() as $p) {
			echo $p->name.'<br>';
			foreach($p->moves()->get() as $m) {
				echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$m->definition()->name.'<br>';
			}
		}

	}

	public function setMOTD() {
		file_put_contents('../motd.txt', Input::get('value'));
		echo 'File Contents: '.file_get_contents('../motd.txt');
		MessageController::sendMessageToAllUsers(Auth::user()->id, 'News Updated', 'The game news has been updated.<br><br>'.Input::get('value'));
	}

	public static function getAllActiveUsers() {
		$out = [];
		foreach(Trainer::all() as $t) {
			if(!in_array($t->user(), $out)) $out[] = $t->user();
		}
		return $out;
	}
	
	public static function searchCampaigns() {
		return Response::json(Campaign::where('name', 'LIKE', '%'.Input::get('value').'%')->get());
	}
	
	public function showLogin() {
		return View::make('login');
	}

	public function doLogin() {
		$rules = array(
			'username' => 'required',
			'password' => 'required|alphaNum|min:3'
		);

		$validator = Validator::make(Input::all(), $rules);

		if($validator->fails()) {
			return Redirect::to('login')->withErrors($validator)->withInput(Input::except('password'));
		} else {
			$user = User::where('username', '=', Input::get('username'));
			if($user->count() == 0) return Redirect::to('login')->withErrors(array('message' => 'Invalid username or password.'));
			$user = $user->first(); 
			if($user->password == hash('SHA256', Input::get('password'))) {
				Auth::login($user, true);
				return Redirect::to('/');
			} else {
				return Redirect::to('login')->with('login_errors', true)->withErrors(array('message' => 'Invalid username or password.'));
			}
		}	
	}

	public function createAccount() {
		$rules = array(
			'register_username' => 'required|unique:accounts,username',
			'register_password' => 'required|alphaNum|min:5|confirmed',
			'register_email' => 'required|unique:accounts,email|email'
		);

		$validator = Validator::make(Input::all(), $rules);
		$validator->getPresenceVerifier()->setConnection('user_sql');
		if($validator->fails()) {
			return Redirect::to('login')->withErrors($validator)->withInput(Input::except('password'));
		} else {
			$user = new User;
			$user->username = Input::get('register_username');
			$user->password = hash('SHA256', Input::get('register_password'));
			$user->email = Input::get('register_email');
			$user->timestamps = false;
			$user->save();
			Auth::login($user, true);
			return Redirect::to('/');
		}

	}

	public function doLogout() {
		Auth::logout();
		return Redirect::to('login');
	}

}