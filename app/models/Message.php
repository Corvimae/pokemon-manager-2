<?php

class Message extends Eloquent {
	protected $table = 'user_messages';
	
	public function from() {
		if($this->from_id == 0) return 'System';
		return User::find($this->from_id)->username;
	}
	
	public function seen() {
		return strcmp($this->viewed_at, '1901-01-01 00:00:00+00') > 0 ? 1 : 0;
	}
	protected function getDateFormat() {
			return 'Y-m-d H:i:sO';
	}
}

?>