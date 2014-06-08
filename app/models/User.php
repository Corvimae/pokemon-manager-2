<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface {
	protected $connection = 'user_sql';
	protected $table = "accounts";

	protected $primaryKey = 'id';


	public function getAuthIdentifier() {
        return $this->id;
    }

    public function getAuthPassword() {
        return $this->password;
    }

    public function getReminderEmail() {
        return $this->email;
    }

    public function pokemon() {
        return $this->hasMany("Pokemon");
    }

    public function unassignedPokemon() {
        return $this->pokemon()->where('trainer_id', 0)->orderBy("position");;
    }

    public function trainers() {
        return $this->hasMany('Trainer');
    }

    public function belongsToGame($game) {
        foreach($this->trainers()->get() as $t) {
            if($t->belongsToGame($game) || $this->isSpecificGM($game)) return true;
        }

        return false;
    }

    public function permissions() {
        return $this->hasMany("Permission");
    }

    public function hasPermission($permission) {
        $p_id = PermissionDefinition::where('name', $permission)->firstOrFail()->id;
        return count($this->permissions()->where('permission', $p_id)->get()) > 0;
    }

    public function isAdministrator() {
        return $this->hasPermission('Admin');
    }

    public function isGM() {
        return $this->hasPermission('GM') || $this->isAdministrator();
    }

    public function isSpecificGM($game) {
        return (count($this->permissions()->where('permission', 2)->where('value', $game)->get()) > 0) || $this->isAdministrator();
    }
    
    public function isSpecificGMIgnoreAdmin($game) {
	    return (count($this->permissions()->where('permission', 2)->where('value', $game)->get()) > 0);
    }
    
    public function getAllGMCampaigns() {
    	$out = array();
    	foreach(Campaign::all() as $c) {
	    	if($this->isSpecificGMIgnoreAdmin($c->id)) $out[] = $c;
    	}
    	return $out;
    }
	
	public function hasPermissionValue($permission, $value) {
		$p_id = PermissionDefinition::where('name', $permission)->first();
        if(count($p_id) == 0) return false;
		$per_row = $this->permissions()->where('permission', $p_id->id)->first();
		return count($per_row) > 0 && $per_row->value == $value;
	}
	
	public function messages() {
		return $this->hasMany('Message')->orderBy('created_at', 'desc');
	}
    public function newMessages() {
        return $this->messages()->where('viewed_at', '=', date('0000-00-00 00:00:00'))->orderBy('created_at', 'desc');
    }
    public function countNewMessages() {
        return count($this->newMessages()->get());
    }
    
    public function getRememberToken() {
	    return $this->remember_token;
	}
	
	public function setRememberToken($value) {
	    $this->remember_token = $value;
	}
	
	public function getRememberTokenName() {
	    return 'remember_token';
	}
}
