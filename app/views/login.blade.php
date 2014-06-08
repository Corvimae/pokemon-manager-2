@extends('layout')
@section('title', 'Pokemon Manager 2')
@section('script')
<script type="text/javascript">
$(function() {

	/*if ($.browser.webkit) {
	    $('input[type=password]').attr('autocomplete', 'off');
	    $('input[type="text"]').attr('autocomplete', 'off');
	}*/

	function loginVM() {
		var self = this;
		self.loginMode = ko.observable(0);

		self.setLoginMode = function(mode) {
			self.loginMode(mode);
		}
	}

	ko.applyBindings(new loginVM());
});
</script>
@stop
@section('content')
		
		<div class="existing-account" data-bind="visible: $root.loginMode() == 0">
			<div class="login-subtitle">Login to an Existing Account
				<div class="mode-switch" data-bind="click: function() { return $root.setLoginMode(1); }">...or make a new account.</div>
			</div>
			
			<div class="form-holder">
			{{ Form::open(array('url' => 'login', 'autocomplete' => 'off')) }}

				<p class="login-errors"> 
					{{ $errors->first('username') }}
					{{ $errors->first('password') }}
					{{ $errors->first('message') }}
				</p>

					{{ Form::label('username', 'Username') }}
					{{ Form::text('username', Input::old('username'), array('placeholder' => '')) }}

					{{ Form::label('password', 'Password') }}
					{{ Form::password('password') }}
				<button type="submit"><i class="fa fa-check"></i></button>

			{{ Form::close() }}
			</div>

		</div>
		<div class="new-account" data-bind="visible: $root.loginMode() == 1">
			<div class="login-subtitle">Create a New Account
				<div class="mode-switch" data-bind="click: function() { return $root.setLoginMode(0); }">...or login to an existing account.</div>
			</div>
			<div class="form-holder">
			{{ Form::open(array('url' => 'createAccount', 'autocomplete' => 'off')) }}

			<p>
				{{ $errors->first('register_username') }}
				{{ $errors->first('register_password') }}
				{{ $errors->first('register_password_confirmation') }}
				{{ $errors->first('register_email') }}

			</p>

				{{ Form::label('register_username', 'Username') }}
				{{ Form::text('register_username', Input::old('register_username'), array('placeholder' => '')) }}

				{{ Form::label('register_password', 'Password') }}
				{{ Form::password('register_password') }}

				{{ Form::label('register_password_confirmation', 'Verify Password') }}
				{{ Form::password('register_password_confirmation') }}

				{{ Form::label('register_email', 'Email') }}
				{{ Form::text('register_email', Input::old('register_email'), array('placeholder' => '')) }}

				<button type="submit"><i class="fa fa-check"></i></button>
			{{ Form::close() }}
			</div>

		</div>

@stop