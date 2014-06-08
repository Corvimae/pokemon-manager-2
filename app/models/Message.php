<?

class Message extends Eloquent {
	protected $table = 'user_messages';
	
	public function from() {
		if($this->from_id == 0) return 'System';
		return User::find($this->from_id)->username;
	}
	
	public function seen() {
		return !(int)$this->viewed_at ? 0 : 1;
	}

}

?>