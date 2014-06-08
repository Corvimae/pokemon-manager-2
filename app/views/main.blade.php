@extends('layout')
@section('title', 'Pokemon Viewer')
@section('script')
	<script src="js/jquery-ui-1.10.4.custom.min.js"></script>
	<script src="js/knockout-sortable.js"></script>
	<script type="text/javascript">
		$(function() {


			$(".pkmn-record-add").click(function() {
				window.location = "/pokemon/new";
			});

			$(".trainer-submit").click(function(ev) {
				$.getJSON("/api/v1/trainer/add/" + $(".trainer-name-input").val(), function(data) {
					window.location = "/";
				});
			});

			
			function Pokemon(id, species, name, sprite, level) {
				return {id: id, species: species, name: name, sprite: sprite, level: level};
			}
			
			function mainVM() {
				var self = this;
				
				self.trainerList = ko.observableArray([]);
				@foreach($user->trainers()->get() as $t)
					var nobj = {id: {{$t->id}}, name: "{{$t->name}}", pokemon: ko.observableArray([])};
					@foreach($t->pokemon()->get() as $p)
						nobj.pokemon.push(new Pokemon({{$p->id}}, "{{$p->species()->name}}", "{{$p->name}}", "{{$p->species()->sprite()}}", {{$p->hidden ? -1 : $p->level()}}));
					@endforeach
					self.trainerList.push(nobj);
				@endforeach
				
				self.unassignedList = ko.observableArray([]);
				@foreach($user->unassignedPokemon()->get() as $p)
					self.unassignedList.push(new Pokemon({{$p->id}}, "{{$p->species()->name}}", "{{$p->name}}", "{{$p->species()->sprite()}}", {{$p->hidden ? -1 : $p->level()}}));
				@endforeach
					
				self.displayLevel = function(row) {
					if(row.level == -1) return row.species;
					return 'Lv. ' + row.level + ' ' + row.species;
				}
				
				self.goToPokemon = function(row, event) {
					if(event.ctrlKey || event.metaKey) {
						window.open("/pokemon/" + row.id, "_blank");
					} else {
						window.location = "/pokemon/" + row.id;
					}
				}
				
				self.goToTrainer = function(row, event) {
				console.log(row);
					if(event.ctrlKey || event.metaKey) {
						window.open("/trainer/" + row.id, "_blank");
					} else {
						window.location = "/trainer/" + row.id;
					}
				}
				
				self.processSort = function(arg) {
					var outstr = "";
					for(var p in arg.targetParent()) {
						outstr += arg.targetParent()[p].id + ",";
					}
					outstr = outstr.substr(0, outstr.length - 1);
					var t_id = $(this).parent().attr("data-id");
					$.getJSON("/api/v1/pokemon/" + arg.item.id + "/update/trainer/" + t_id, function(data) {
						$.getJSON("/api/v1/trainer/" + t_id + "/pokemon/sort/" + outstr);
					});
				}
				
				self.deletePokemon = function(parent, row) {
					parent.pokemon.remove(row);
					$.getJSON("/api/v1/pokemon/" + row.id + "/delete");
				}
				
				self.deleteUnassignedPokemon = function(row) {
					self.unassignedList.remove(row);
					$.getJSON("/api/v1/pokemon/" + row.id + "/delete");
				}
				
			}
			
			ko.applyBindings(new mainVM());
		});
	</script>
@stop
@section('content')
	<div class="motd">{{ file_get_contents('../app/motd.txt'); }}</div>
	<div class="pkmn-name"><div class="user-title">Hello, {{$user->username }}</div></div>
	@if($user->isAdministrator())
		<div class="stat-row"><div class="row-title">You are an administrator.</div></div>
	@elseif($user->isGM())
		<div class="stat-row"><div class="row-title">You are a GM.</div></div>
	@endif
	<div class="stat-row"><div class="row-title">You have {{ count($user->pokemon()->get()) }} Pokemon across {{ count($user->trainers()->get()) }} Trainers.</div></div>
	
	<!-- ko foreach: $root.trainerList -->
		<div class="pkmn-records" data-bind="attr: {'data-id': id }">
			<div class="stat-row header-row trainer-name" data-bind="click: $root.goToTrainer"><div class="row-title" data-bind="text: name"></div></div>
			 <div data-bind="sortable: {data: pokemon, afterMove: $root.processSort}">
				<div class="pkmn-record-shell" data-bind="attr: {'data-id': id}, click: $root.goToPokemon"><div class="pkmn-record-shell-inner">
						<img class="pkmn-sprite" data-bind="attr: {'src': sprite}">
						<div class="pkmn-record-title" data-bind="text: name"></div>
						<div class="pkmn-record-desc" data-bind="text: $root.displayLevel($data)"></div>
						<div class="pkmn-record-delete" data-bind="click: function() { return $root.deletePokemon($parent, $data) }">&times;</div>

				</div></div>
			 </div>
		</div>
	<!-- /ko -->
	<div class="pkmn-records" data-id="0">
		<div class="stat-row header-row"><div class="row-title">Unassigned Pokemon</div></div>
		<div data-bind="sortable: {data: $root.unassignedList, afterMove: $root.processSort}">
			<div class="pkmn-record-shell" data-bind="attr: {'data-id': id}, click: $root.goToPokemon"><div class="pkmn-record-shell-inner">
					<img class="pkmn-sprite" data-bind="attr: {'src': sprite}">
					<div class="pkmn-record-title" data-bind="text: name"></div>
					<div class="pkmn-record-desc" data-bind="text: $root.displayLevel($data)"></div>
					<div class="pkmn-record-delete" data-bind="click: $root.deleteUnassignedPokemon">&times;</div>

			</div></div>
		 </div>
	<div class="pkmn-record-add">Add New Pokemon</div>
	</div>

	<div class="stat-row header-row trainer-record-add">
		<div class="row-title">Add New Trainer</div>
		<div class="row-content">
			<input type="text" class="stat-input trainer-name-input">
			<button class="trainer-submit">Create</button>
		</div>
	</div>


@stop