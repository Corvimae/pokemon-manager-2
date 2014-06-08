@extends('layout')
@section('title', 'Pokemon Viewer')
@section('script')
	<script src="/js/jquery-ui-1.10.4.custom.min.js"></script>
	<script type="text/javascript">
		$(function() {

			$(".trainer-name").click(function(ev) {
				window.location = "/trainer/" + $(this).parents(".pkmn-records").attr("data-id");
			});
			$(".pkmn-record-shell").click(function(ev) {
				if(ev.ctrlKey) {
					window.open( "/pokemon/" + $(this).attr("data-id"), '_blank');
				} else {
					window.location = "/pokemon/" + $(this).attr("data-id");
				}
			});





		});
	</script>
@stop
@section('content')
	<div class="pkmn-name"><div class="user-title">Active Trainers</div></div>	
	@foreach(Trainer::all() as $t)
		@if($t->belongsToGame($game) && !$t->user()->isGM() && count($t->activePokemon()->get()) > 0)
		<div class="pkmn-records" data-id="{{$t->id}}">
			<div class="stat-row header-row trainer-name"><div class="row-title">{{$t->name}}</div></div>
			@foreach($t->pokemon()->get() as $p)
				<div class="pkmn-record-shell" data-id="{{$p->id}}"><div class="pkmn-record-shell-inner">
						<img class="pkmn-sprite" src="{{$p->species()->sprite()}}">
						<div class="pkmn-record-title">{{$p->name}}</div>
						<div class="pkmn-record-desc">@if(!$p->hidden)Lv. {{$p->level()}}@endif {{$p->species()->name}}</div>
			</div></div>
			@endforeach
		</div>
		@endif
	@endforeach



@stop