@extends('layout')
@section('title', 'Pokemon Viewer')
@section('includes')
	<link rel=stylesheet type='text/css' href='/css/select2.css' />
@stop
@section('script')
	<script src="/js/typeahead.bundle.js"></script>
	<script src="/js/select2.min.js"></script>
	<script type="text/javascript">
		$(function() {
			$(".pkmn-record-shell").click(function(ev) {
				window.location = "/pokemon/" + $(this).attr("data-id");
			});

			var classes = new Bloodhound({
			  datumTokenizer: function(d) { return Bloodhound.tokenizers.whitespace(d.name); },
			  queryTokenizer: Bloodhound.tokenizers.whitespace,
			  local: [
			  @foreach(TrainerClassDefinition::all() as $n)
			    { name: '{{$n->name}}', id:'{{$n->id}}' },
			  @endforeach
			  ]
			});	

			classes.initialize();

			var selClass;

			$(".class-name-input").typeahead({autoselect: true}, {
			  displayKey: 'name',
			  source: classes.ttAdapter()
			}).on('typeahead:selected', function (obj, datum) {
			    selClass = datum;
			});

			$("#class-add").click(function() {
				if(selClass == undefined) return;
				$.getJSON("/api/v1/trainer/{{$trainer->id}}/class/add/" + selClass.id, function(data) {
					location.reload();
				});
			});
			
			$("#campaignSelector").select2({
				placeholder: 'Search for a Campaign',
				minimumInputLength: 5,
				ajax: {
					url: '/api/v1/campaign/search',
					data: function(term, page) {
						return {
							value: term
						}
					}, 
					results: function(data, page) {
						return  {results: data};
					}
				}, 
				formatResult: campaignFormatResult,
				formatSelection: campaignFormatSelection,
				initSelection: function (element, callback) {
			        var data = {id: {{$trainer->campaign()->id}}, name: '{{ $trainer->campaign()->name }}'};
			        console.log(data);
			        callback(data);
			    }
			});
			
			$("#campaignSelector").select2('val', '{{ $trainer->campaign()->name }}');
			
			$("#campaignSelector").on('select2-selecting', function(e) {
				$.getJSON('/api/v1/trainer/{{$trainer->id}}/campaign/update/' + e.val);
			});
						
			
			function campaignFormatResult(item) {
				var markup = item.name;
				return markup;
			}
			
			function campaignFormatSelection(item) {
				return item.name;
			}
			
			
		});
		
		
	</script>
@stop
@section('content')
	<div class="pkmn-name"><div class="user-title">{{$trainer->name }}</div></div>
	<div class="stat-row"><div class="row-title">{{$trainer->name}} has {{ count($trainer->pokemon()->get()) }} Pokemon ({{count($trainer->pokemon()->where('active', true)->get())}} active).</div></div>
	@if(Auth::user()->isSpecificGM($trainer->primaryCampaign())) 
	<div class="stat-row"><div class="row-title">Trainer ID</div><div class="row-content">{{$trainer->id}}</div></div>
	@endif

	<div class="stat-row"><div class="row-title">Campaign</div><div class="row-content"><hidden id="campaignSelector"></div></div>

	<div class="pkmn-records trainer-pkmn-record" data-id="0">
		<div class="stat-row header-row"><div class="row-title">Pokemon</div></div>
		@foreach($trainer->pokemon()->get() as $p)
			<div class="pkmn-record-shell" data-id="{{$p->id}}"><div class="pkmn-record-shell-inner">
					<img class="pkmn-sprite" src="{{$p->species()->sprite()}}">
					<div class="pkmn-record-title">{{$p->name}}</div>
					<div class="pkmn-record-desc">@if(!$p->hidden)Lv. {{$p->level()}} @endif {{$p->species()->name}}</div>
					<div class="pkmn-record-delete">&times;</div>
			</div></div>
		@endforeach
	</div>
	@foreach($trainer->classes()->get() as $c) 
		<div class="stat-row header-row"><div class="row-title">{{$c->definition()->name}}</div></div>
	@endforeach

	<div class="stat-row header-row trainer-record-add">
		<div class="row-title">Add Class</div>
		<div class="row-content">
			<input type="text" class="stat-input class-name-input ability-input" id="class-add-input">
			<button class="trainer-submit" id="class-add">Add</button>
		</div>
	</div>

@stop