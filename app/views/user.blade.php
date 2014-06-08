@extends('layout')
@section('title', 'Pokemon Viewer')
@section('script')
	<script src="/js/jquery-ui-1.10.4.custom.min.js"></script>
	<script type="text/javascript">
	</script>
@stop
@section('content')
	<div class="pkmn-name"><div class="user-title">User Data for {{$user->username }}</div></div>
	<div class="stat-column">
		<div class="stat-row"><div class="row-title">User Permissions</div></div>
		@foreach($user->permissions()->get() as $p)
			<div class="stat-row permission-row">
				<span class="permission-title">{{$p->definition()->name}}_{{$p->value}}</span><span class="permission-desc"> - {{$p->definition()->description}}</span>
			</div>
		@endforeach
	</div>
	<div class="stat-column">
		<div class="stat-row"><div class="row-title">{{ count($user->pokemon()->get()) }} Pokemon across {{ count($user->trainers()->get()) }} Trainers.</div></div>
		<div class="stat-row permission-row">
			<span class="permission-title">{{count($user->pokemon()->where('active', 1)->get())}} active.</span>
		</div>
		<div class="stat-row permission-row">
			<? $recent = $user->pokemon()->orderBy('updated_at', 'DESC')->first(); ?>
			<span class="permission-title">Most recently edited: <a href="/pokemon/{{$recent->id}}">{{$recent->name}}</a></span>

		</div>
		<div class="stat-row permission-row">
			<? $newest = $user->pokemon()->orderBy('created_at', 'DESC')->first(); ?>
			<span class="permission-title">Newest: <a href="/pokemon/{{$newest->id}}">{{$newest->name}}</a></span>
		</div>

	</div>

@stop