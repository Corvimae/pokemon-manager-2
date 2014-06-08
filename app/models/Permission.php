<?

class Permission extends Eloquent {
	protected $table = 'user_permissions';

	public function definition() {
		return PermissionDefinition::find($this->permission);
	}
}

?>