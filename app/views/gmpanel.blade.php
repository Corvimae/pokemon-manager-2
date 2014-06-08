@extends('layout')
@section('title', 'Pokemon Viewer')
@section('script')
	<script src="/js/jquery-ui-1.10.4.custom.min.js"></script>
	<script type="text/javascript">
		$(function() {
			function calculatorViewModel() {
				var self = this;
				self.baseRate = ko.observable(0);

				self.attraction = ko.observable(false);
				self.badlyPoisoned = ko.observable(false);
				self.burn = ko.observable(false);
				self.confused = ko.observable(false);
				self.critted = ko.observable(false);
				self.flinched = ko.observable(false);
				self.freeze = ko.observable(false);
				self.paralysis = ko.observable(false);
				self.poison = ko.observable(false);
				self.sleep = ko.observable(false);
				
				self.healthFormula = ko.observable("{{$campaign->health_formula}}");
				self.isPTU = ko.observable({{$campaign->isPTU}});

				self.damageModifiers = ko.observableArray([
					{name: "100%", value: -15},
					{name: "75%", value: -5},
					{name: "50%", value: 5},
					{name: "25%", value: 15},
					{name: "1 HP", value: 25}
				]);

				self.wildModifiers = ko.observableArray([
					{name: "1-5", value: 20},
					{name: "6-10", value: 10},
					{name: "11-20", value: 5},
					{name: "21-30", value: 0},
					{name: "31-40", value: -5},
					{name: "41-60", value: -15},
					{name: "61-80", value: -25},
					{name: "81-99", value: -35},
					{name: "100", value: -40}
				]);

				self.selectedDamageModifier = ko.observable();
				self.selectedWildModifier = ko.observable();

				self.captureValue = ko.computed(function() {
					return parseInt(self.baseRate()) + getCaptureValue(self.attraction(), 3) + getCaptureValue(self.badlyPoisoned(), 10) + getCaptureValue(self.burn(), 5) + getCaptureValue(self.confused(), 5)
					+ getCaptureValue(self.critted(), 10) + getCaptureValue(self.flinched(), 5) + getCaptureValue(self.freeze(), 15) + getCaptureValue(self.paralysis(), 7) + getCaptureValue(self.poison(), 5)
					+ getCaptureValue(self.sleep(), 10) + (self.selectedDamageModifier() == undefined ? 0 : self.selectedDamageModifier().value)
					+ (self.selectedWildModifier() == undefined ? 0 : self.selectedWildModifier().value);
				});


				self.saveStats = function() {
				console.log(self.healthFormula());
					$.post("/api/v1/campaign/{{$campaign->id}}/formula/health/update", {value: self.healthFormula()}, function() {});
					$.post("/api/v1/campaign/{{$campaign->id}}/setting/ptu/update", {value: self.isPTU()}, function() {});

				}
				function getCaptureValue(obs, val) {
					return obs ? val : 0;
				}

			};

			ko.applyBindings(new calculatorViewModel());
			
			$(".motd-submit").click(function() {
				$.post('/api/v1/gm/motd', { value: $(".motd-box").val() });
			});
		});
	</script>
@stop
@section('content')
<? 	$game = $campaign->id ?>
	<div class="pkmn-name"><div class="user-title">{{$campaign->name}} GM Panel</div></div>
	<div class="stat-row">
	<div class="row-title">Other Panels</div><div class="row-content">
		@foreach(Auth::user()->getAllGMCampaigns() as $c)
			@if($c->id != $game) <a class="panel-link" href="/gmpanel/{{$c->id}}">{{$c->name}}</a>@endif
		@endforeach
	</div>
	</div>
	<div class="gm-col">
		<div class="panel-frame">
			<div class="panel-title">Statistics</div>
			<ul class="gm-stats">
				<?
					$trainers = array();
					foreach(Trainer::all() as $t) {
						if($t->belongsToGame($game) && count($t->activePokemon()->get()) > 0) $trainers[] = $t;
					}
					
				?>
				<li>There are {{count($trainers)}} active Trainers</li>
				<?
					$active_pkmn = array();
					foreach($trainers as $t) {
						foreach($t->activePokemon()->get() as $p) {
							$active_pkmn[] = $p;
						}
					}
				?>
			
				<li>There are {{count($active_pkmn)}} active Pokemon</li>

				<? 
					$most_recent_val = date(0);
					$most_recent_item = null;
					$highest_val = 0;
					$highest_item = null;
					
					$total_level = 0;
					$total_loyalty = 0;
					foreach($active_pkmn as $p) {
						if($p->updated_at > $most_recent_val) {
							$most_recent_val = $p->updated_at;
							$most_recent_item = $p;
						}
						if($p->level() > $highest_val) {
							$highest_val = $p->level();
							$highest_item = $p;
						}
						
						$total_level += $p->level();
						$total_loyalty += $p->loyalty;
					}
				?>
				@if(!is_null($most_recent_item))
				<li>The most recently updated Pokemon is <a href="/pokemon/{{$most_recent_item->id}}">{{$most_recent_item->name}}</a></li> 
				@endif
				@if(!is_null($highest_item))
				<li>The highest level Pokemon is <a href="/pokemon/{{$highest_item->id}}">{{$highest_item->name}}</a></li> 
				@endif
				
				
				@if(count($active_pkmn) != 0)
				<?  

				$avg_level = $total_level / count($active_pkmn);
				$avg_loyalty = $total_loyalty / count($active_pkmn);

				$stdev_level = 0;
				$stdev_loyalty = 0;
				foreach($active_pkmn as $p) {
					$stdev_level += pow($p->level() - $avg_level, 2);
					$stdev_loyalty += pow($p->loyalty - $avg_loyalty, 2);
				}

				$stdev_level = sqrt($stdev_level / (count($active_pkmn)));
				$stdev_loyalty = sqrt($stdev_loyalty / (count($active_pkmn)));

				?>
				 <li>The average level across all active Pokemon is {{round($avg_level,2)}} (&plusmn; {{round($stdev_level,2)}})</li> 
				 <li>The average loyalty across all active Pokemon is {{round($avg_loyalty,2)}} (&plusmn; {{round($stdev_loyalty,2)}})</li> 
				 @endif
				 
			</ul>
		</div>
		<div class="panel-frame trainer-list gm-trainers">
			<div class="panel-title" style="margin-bottom: 10px">GM Trainers</div>
			@foreach(Trainer::orderBy('user_id')->get() as $trainer)
				@if($trainer->user()->isSpecificGMIgnoreAdmin($game) && $trainer->campaign()->id == $game)
					<div class="stat-row gm-trainer-row"><div class="row-title"><a href="/trainer/{{$trainer->id}}">{{$trainer->name}}</a></div></div>
					@foreach($trainer->pokemon()->get() as $pkmn)
						<a href="/pokemon/{{$pkmn->id}}"><img class="gm-pkmn-sprite" src="{{$pkmn->species()->sprite()}}"></a>
					@endforeach
				@endif
			@endforeach
		</div>
	</div>
	<div class="gm-col">
		<div class="panel-frame trainer-list">
			<div class="panel-title" style="margin-bottom: 10px">Active Trainers</div>
			@foreach($trainers as $trainer)
				<div class="stat-row gm-trainer-row"><div class="row-title"><a href="/trainer/{{$trainer->id}}">{{$trainer->name}}</a></div></div>
				@foreach($trainer->activePokemon()->get() as $pkmn)
					<a href="/pokemon/{{$pkmn->id}}"><img class="gm-pkmn-sprite" src="{{$pkmn->species()->sprite()}}"></a>
				@endforeach
			@endforeach
		</div>
		<div class="panel-frame trainer-list">
			<div class="panel-title" style="margin-bottom: 10px">User Data</div>
				@foreach(DB::table('player_pokemon_data')->groupBy('user_id')->get() as $p)
					<? $activeUser = User::find($p->user_id); ?>
					@if($activeUser->belongsToGame($game)) <div class="stat-row gm-user-row"><a href='/user/{{$activeUser->id}}'>{{$activeUser->username}}</a></div> @endif
				@endforeach
		</div>
	</div>
	<div class="gm-col">
		<div class="panel-frame trainer-list">
			<div class="panel-title" style="margin-bottom: 10px">Capture Rate Calculator</div>
			<div class="gm-reference-info">
				<p class="gm-capture-rate"> Capture Rate: <span data-bind="text: $root.captureValue()"></span></p>
				<p class="baserate">Base Rate: <input class="stat-input stat-input-small gm-calculator-input" data-bind="value: $root.baseRate" style="font-size: 16px;"></p>
				<p class="checklist">Attraction: <input type="checkbox" data-bind="checked: $root.attraction" /></p>
				<p class="checklist">Badly Poisoned: <input type="checkbox" data-bind="checked: $root.badlyPoisoned" /></p>
				<p class="checklist">Burned: <input type="checkbox" data-bind="checked: $root.burn" /></p>
				<p class="checklist">Confused: <input type="checkbox" data-bind="checked: $root.confused" /></p>
				<p class="checklist">Critted: <input type="checkbox" data-bind="checked: $root.critted" /></p>
				<p class="checklist">Flinched: <input type="checkbox" data-bind="checked: $root.flinched" /></p>
				<p class="checklist">Frozen: <input type="checkbox" data-bind="checked: $root.freeze" /></p>
				<p class="checklist">Paralysed: <input type="checkbox" data-bind="checked: $root.paralysis" /></p>
				<p class="checklist">Poison: <input type="checkbox" data-bind="checked: $root.poison" /></p>
				<p class="checklist">Sleep: <input type="checkbox" data-bind="checked: $root.sleep" /></p>
				<p class="dropdown">Damage: <select data-bind="options: $root.damageModifiers, optionsText: 'name', value: selectedDamageModifier"></select></p>
				<p class="dropdown">Level: <select data-bind="options: $root.wildModifiers, optionsText: 'name', value: selectedWildModifier"></select></p>
			</div>
		</div>
		<div class="panel-frame trainer-list">
			<div class="panel-title" style="margin-bottom: 10px">Campaign Settings</div>
				<div class="gm-reference-info"
					<label for='ptu-check'>PTU?</label><input type='checkbox' class="gm-settings-check" name='ptu-check' data-bind="checked: $root.isPTU">
				</div>
				<div class="stat-row gm-user-row">Stat Calculations</div>
				<div class="gm-reference-info small-gm-info">
					Available Variables:<br>
					{level}: Pokemon Level<br>
					{[stat]_total}: Total stat value (e.g. {attack_total})<br>
					{[stat]_base}: Base stat value<br>
					{[stat]_add}: Add stat value<br>
					Stats: hp, attack, defense, spattack, spdefense, speed
				
				</div>
				<div class="gm-reference-info">
					Health: <input type="text" class="gmSettingBox" id="calcHealth" data-bind="value: healthFormula">
					
					<button id="saveStats" data-bind="click: $root.saveStats">Save</button>
				</div>
				
		</div>
		<div class="panel-frame trainer-list">
			<div class="panel-title" style="margin-bottom: 10px">Reference</div>
				<div class="stat-row gm-user-row">Campaign IDs</div>
				<div class="gm-reference-info">
					0 - Foundations <br>
					1 - Sacrem Region
				</div>
				<div class="stat-row gm-user-row">Type IDs</div>
				<div class="gm-reference-info">
					@foreach(Type::all() as $t) 
					{{$t->id}} - {{$t->name}} <br>
					@endforeach
				</div>	
				<div class="stat-row gm-user-row">Contest Effects</div>
				<div class="gm-reference-info">
					@foreach(ContestEffect::all() as $t) 
					{{$t->id}} - {{$t->name}} <br>
					@endforeach
				</div>				
		</div>
		<div class="panel-frame trainer-list">
			<div class="panel-title" style="margin-bottom: 10px">Message of the Day</div>
			<textarea class="motd-box">{{ file_get_contents('../motd.txt'); }}</textarea>
			<button class="submit motd-submit">Save</button>
	</div>

@stop