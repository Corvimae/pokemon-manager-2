<? $user = Auth::user(); 

?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>@if(isset($user)) {{ $user->countNewMessages() > 0 ? '('.$user->countNewMessages().')' : ''}} @endif @yield('title')</title>
		<link href='http://fonts.googleapis.com/css?family=Lato:300,400,700,400italic' rel='stylesheet' type='text/css'>
		<link href='font-awesome/css/font-awesome.min.css' rel='stylesheet' type='text/css'>
		<link href="css/main.css" rel="stylesheet" type="text/css">
		@yield('includes')
	</head>
	<body>
	
		<div class="navbar">
			<a class="nav-title" href="/">Pokemon Manager 2</a>
			<a class="nav-item" href="/trainers/1">Active Trainers</a>
			@if(isset($user))
			
			@if($user->isGM()) <a class="nav-item" href="/gmpanel/{{$user->getAllGMCampaigns()[0]->id}}">GM Panel </a>@endif
			<a class="nav-messages {{$user->countNewMessages() > 0 ? 'active' : ''}}" href="{{$user->countNewMessages() > 0 ? '/messages/unread' : '/messages'}}"><i class="fa fa-envelope"></i></a>
			@if($user->countNewMessages() > 0) <a class="nav-new-messages" href="{{$user->countNewMessages() > 0 ? '/messages/unread' : '/messages'}}">{{$user->countNewMessages()}}</a>@endif
			
			<a class="nav-exit" href="/logout">Logout</a>
			@endif

		</div>
		@yield('content')
		<script src="js/jquery-2.1.0.js"></script>
		<script src="js/knockout.js"></script>
		<script src="js/knockout.bindings.typeahead.min.js"></script>

		@yield('script')
	</body>
</html>
