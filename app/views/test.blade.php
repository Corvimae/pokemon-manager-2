@extends('layout')
@section('title', 'Pokemon Viewer')
@section('includes')
	<link rel='stylesheet' type='text/css' href='/css/toggle-switch.css' />
@stop
@section('script')
	<script src="/js/typeahead.bundle.js"></script>
	<script src="/js/jquery-ui-1.10.4.custom.min.js"></script>
	<script type="text/javascript">
		$(function() {

			var id = {{$pkmn->id}};

			function setAnimationPosition() {
				$("#pkmn-anim").css('margin-left', -1*$("#pkmn-anim").width()/2);
				$("#pkmn-anim").css('margin-top', -1*$("#pkmn-anim").height()/2);
				console.log("Setting animation position");
			}

			$(".battle-bg").css("background-image", "url(http://cdn.acceptableice.com/pkmn/battle-bg-" + (Math.floor(Math.random()*2) + 1) + ".png)");

			var natures = new Bloodhound({
			  datumTokenizer: function(d) { return Bloodhound.tokenizers.whitespace(d.nature); },
			  queryTokenizer: Bloodhound.tokenizers.whitespace,
			  local: [
			  @foreach(Nature::All() as $n)
			    { nature: '{{$n->name}}', id:'{{$n->id}}' },
			  @endforeach
			  ]
			});

			var moves = new Bloodhound({
			  datumTokenizer: function(d) { return Bloodhound.tokenizers.whitespace(d.move); },
			  queryTokenizer: Bloodhound.tokenizers.whitespace,
			  local: [
			  @foreach(MoveDefinition::All() as $n)
			    { move: '{{$n->name}}', id:'{{$n->id}}' },
			  @endforeach
			  ]
			});

			var abilities = new Bloodhound({
			  datumTokenizer: function(d) { return Bloodhound.tokenizers.whitespace(d.ability); },
			  queryTokenizer: Bloodhound.tokenizers.whitespace,
			  local: [
			  @foreach(AbilityDefinition::All() as $n)
			    { ability: '{{$n->name}}', id:'{{$n->id}}' },
			  @endforeach
			  ]
			});

			var capabilities = new Bloodhound({
			  datumTokenizer: function(d) { return Bloodhound.tokenizers.whitespace(d.capability); },
			  queryTokenizer: Bloodhound.tokenizers.whitespace,
			  local: [
			  @foreach(CapabilityDefinition::All() as $n)
			    { capability: '{{$n->name}}', id:'{{$n->id}}' },
			  @endforeach
			  ]
			});

			var species = new Bloodhound({
			  datumTokenizer: function(d) { return Bloodhound.tokenizers.whitespace(d.species); },
			  queryTokenizer: Bloodhound.tokenizers.whitespace,
			  local: [
			  @foreach(Species::All() as $n)
			    { species: '{{ addslashes($n->name) }}', id:'{{$n->id}}' },
			  @endforeach
			  ]
			});

			var heldItems = new Bloodhound({
			  datumTokenizer: function(d) { return Bloodhound.tokenizers.whitespace(d.item); },
			  queryTokenizer: Bloodhound.tokenizers.whitespace,
			  local: [
			  @foreach(HeldItem::All() as $n)
			    { item: '{{ addslashes($n->name) }}', id:'{{$n->id}}' },
			  @endforeach
			  ]
			});

			natures.initialize();
			abilities.initialize();
			moves.initialize();
			capabilities.initialize();
			species.initialize();
			heldItems.initialize();


			$("#pkmn-anim").one('load', function() {
	  			setAnimationPosition();
			}).each(function() {
			  if(this.complete) $(this).load();
			});


			$("#nature-input").typeahead({autoselect: true}, {
				  displayKey: 'nature',
				  source: natures.ttAdapter()
			});


			$("#helditem-input").typeahead({autoselect: true}, {
				  displayKey: 'item',
				  source: heldItems.ttAdapter()
			});


			$("#speciesEdit").typeahead({autoselect: true}, {
			  displayKey: 'species',
			  source: species.ttAdapter()
			});

			//---- Edit Mode
			var activeType = 0;
			var typeSelectorMode = 0;
			var typeSelectorElem = undefined;

			var editMode = {{$shouldEdit}};

			if(editMode) {
				setupEditMode();
				viewmodel.isEditing(true);
			}

			var speciesName = "{{$pkmn->species()->name}}";
			var prevAbilityValue = "";

			function setupEditMode() {
				editMode = true;

				setAsEditable(".name-row");
				setAsEditable("#health-row");
				setAsEditable("#attack-row");
				setAsEditable("#defense-row");
				setAsEditable("#spattack-row");
				setAsEditable("#spdefense-row");
				setAsEditable("#speed-row");
				setAsEditable(".nature-row");
				setAsEditable(".loyalty-row");
				setAsEditable(".helditem-row");

				$(".move-list").sortable("option", "disabled", false);
				$(".capability-list").sortable("option", "disabled", false);

				$(".move-shell-inner").each(function(i, v) {
					$(this).append("<div class='delete-move'>&times;</div>");
				});

				$(".capability-shell-inner").each(function(i, v) {
					$(this).append("<div class='delete-capability'>&times;</div>");
				});

				$(".move-list").append("<div class='move-add'>Add Move</div>");

				$(".capability-list").append("<div class='add-capability'>Add Capability</div>");

		
				$(".ability-row").each(function(i, v) {
					$(this).find(".row-content").html("<input class='stat-input ability-input' value='" + $(this).find(".row-content").text() + "'>");
					$(this).find(".ability-input").typeahead({autoselect: true}, {
					  displayKey: 'ability',
					  source: abilities.ttAdapter()
					});
				});

				$(".ability-input").on("typeahead:opened", function() {
					prevAbilityValue = $(this).typeahead('val');
				});
				$(document).on("typeahead:closed", ".ability-row .ability-input", function() {
					var count = 0;
					$(".ability-input").each(function(i,v) { 
						if($(this).hasClass("tt-input") && (($(this).typeahead("val")[0] == undefined) || ($(this).typeahead("val")[0].length == 0))) {
							count++; 
						}
					});
					if(count == 0) {
						createNewAbilityRow(); 
					} else if (count > 1) {
						$(this).parents(".ability-row").remove();
					}
					var base = this;
					var ab_id = $(this).parents(".ability-row").attr("data-id");
					if($(this).typeahead("val")[0] == undefined || $(this).typeahead("val")[0].length == 0) {
						$.getJSON("/api/v1/pokemon/" + id + "/remove/ability/" + ab_id);
					} else {
						$.getJSON("/api/v1/pokemon/" + id + "/insert/ability/" + $(this).typeahead("val")[0].replace(' ', '-'), function(data) {
							 $(base).parents(".ability-row").attr("data-id", data);
							 console.log(data);
						});
					}
				});

				function createNewAbilityRow() {
					console.log("Making new ability row");
					var identifier = ".ability-row:last";
					if($(identifier).length == 0) identifier = ".nature-row";
					$(identifier).after('<div class="stat-row ability-row" data-id="0"><div class="row-title">Ability</div> <div class="row-content"><input class="stat-input ability-input"></div></div>');
					$(".ability-row:last").find(".ability-input").typeahead({autoselect: true}, {
					  displayKey: 'ability',
					  source: abilities.ttAdapter()
					});
				}

				createNewAbilityRow();
			};

			function closeEditMode() {
				editMode = false;
				console.log("Closing edit mode");
				setAsUneditable(".name-row");
				setAsUneditable(".nature-row");
				setAsUneditable("#health-row");
				setAsUneditable("#attack-row");
				setAsUneditable("#defense-row");
				setAsUneditable("#spattack-row");
				setAsUneditable("#spdefense-row");
				setAsUneditable("#speed-row");
				setAsUneditable(".loyalty-row");
				setAsUneditable(".helditem-row");

				$(".ability-row").each(function(i, v) {
					$(this).find(".row-content").text($(this).find(".ability-input").typeahead("val")[0]);
				});
				$(".ability-row:last").remove();
				$(".delete-capability").each(function(i, v) {
					$(this).remove();
				});
				$(".delete-move").each(function(i, v) {
					$(this).remove();
				});
				$(".move-add").remove();
				$(".add-capability").remove();

				$(".move-list").sortable("option", "disabled", true);
				$(".capability-list").sortable("option", "disabled", true);

			}


			$(".edit-button").click(function() {
				if(editMode) {
					closeEditMode();
					$(this).text("Edit");
					viewmodel.isEditing(false);
				} else {
					setupEditMode();
					$(this).text("Lock");
					viewmodel.isEditing(true);
				}
			})

			$(document).on("change", "#nameEdit", function() {
				$.getJSON("/api/v1/pokemon/" + id + "/update/name/" + $(this).val());
			});
			$(document).on("change", "#hp-base", function() {
				$.getJSON("/api/v1/pokemon/" + id + "/update/stat/base-health/" + $(this).val());
			})
			$(document).on("change", "#attack-base", function() {
				$.getJSON("/api/v1/pokemon/" + id + "/update/stat/base-attack/" + $(this).val());
			})
			$(document).on("change", "#defense-base", function() {
				$.getJSON("/api/v1/pokemon/" + id + "/update/stat/base-defense/" + $(this).val());
			})		
			$(document).on("change", "#spattack-base", function() {
				$.getJSON("/api/v1/pokemon/" + id + "/update/stat/base-spattack/" + $(this).val());
			})		
			$(document).on("change", "#spdefense-base", function() {
				$.getJSON("/api/v1/pokemon/" + id + "/update/stat/base-spdefense/" + $(this).val());
			})		
			$(document).on("change", "#speed-base", function() {
				$.getJSON("/api/v1/pokemon/" + id + "/update/stat/base-speed/" + $(this).val());
			})
			$(document).on("change", "#hp-add", function() {
				$.getJSON("/api/v1/pokemon/" + id + "/update/stat/add-health/" + $(this).val());
			})
			$(document).on("change", "#attack-add", function() {
				$.getJSON("/api/v1/pokemon/" + id + "/update/stat/add-attack/" + $(this).val());
			})
			$(document).on("change", "#defense-add", function() {
				$.getJSON("/api/v1/pokemon/" + id + "/update/stat/add-defense/" + $(this).val());
			})		
			$(document).on("change", "#spattack-add", function() {
				$.getJSON("/api/v1/pokemon/" + id + "/update/stat/add-spattack/" + $(this).val());
			})		
			$(document).on("change", "#spdefense-add", function() {
				$.getJSON("/api/v1/pokemon/" + id + "/update/stat/add-spdefense/" + $(this).val());
			})		
			$(document).on("change", "#speed-add", function() {
				$.getJSON("/api/v1/pokemon/" + id + "/update/stat/add-speed/" + $(this).val());
			})

			$(document).on("change", "#setXP", function() {
				$.getJSON("/api/v1/pokemon/" + id + "/update/xp/" + $(this).val());
			});

			$(document).on("change", "#loyalty-edit", function() {
				$.getJSON("/api/v1/pokemon/" + id + "/update/loyalty/" + $(this).val());
			});


			function setAsEditable(element) {
				$(element).find(".display-row").hide();
				$(element).find(".edit-row").show();
			}

			function setAsUneditable(element, id) {
				$(element).find(".edit-row").hide();
				$(element).find(".display-row").show();
			}


			$(document).on("click", ".move-add", function() {
				$(this).before('<div class="move-shell"><div class="move-shell-inner"><div class="move-input-shell"><input class="stat-input move-input"></div></div></div>');
				$(this).prev(".move-shell").find(".move-input").typeahead({autoselect: true}, {
				  displayKey: 'move',
				  source: moves.ttAdapter()
				});
			});

			$(document).on("click", ".add-capability", function() {
				$(this).before('<div class="capability-shell"><div class="capability-shell-inner"><div class="capability-input-shell"><input class="stat-input capability-input" placeholder="Capability"></div><div class="capability-num-input-shell"><input class="stat-input capability-num-input" placeholder="#"></div><div class="capability-submit">&#x2713;</div></div></div>');
				$(this).prev(".capability-shell").find(".capability-input").typeahead({autoselect: true}, {
				  displayKey: 'capability',
				  source: capabilities.ttAdapter()
				});
			});

			$(document).on("click", ".capability-submit", function() {
				var source = this;
				var cap = $(this).parents(".capability-shell").find(".capability-input").typeahead("val")[0];
				var num = $(this).parents(".capability-shell").find(".capability-num-input").val();
				if(num == undefined || num.length == 0) num = 0;
				if(cap != undefined) {
					$.getJSON("/api/v1/pokemon/" + id + "/insert/capability/" + cap.replace(" ", "-") + "/" + num, function(data) {
						$(source).parents(".capability-shell").before('<div class="capability-shell" data-uniq-id="' + data.uniq_id + '" data-id="' + data.id + '"><div class="capability-shell-inner">' + 
						'<div class="capability-name">' + data.cap + ' ' + (num == 0 ? "" : num) + '</div>' +
						'<div class="delete-capability">&times;</div></div></div>');
						$(source).parents(".capability-shell").remove();
					});
				} else {
					$(this).parents(".capability-shell").remove();
				}
			});




			$(document).on("click", ".delete-move", function() {
				var iid = $(this).parents(".move-shell").attr("data-id");
				var source = this;
				$.getJSON("/api/v1/pokemon/" + id + "/remove/move/" + iid, function() {
					$(source).parents(".move-shell").remove();
				});
			});

			$(document).on("click", ".delete-capability", function() {
				var iid = $(this).parents(".capability-shell").attr("data-uniq-id");
				var source = this;
				$.getJSON("/api/v1/pokemon/" + id + "/remove/capability/" + iid, function() {
					$(source).parents(".capability-shell").remove();
				});
			});



			$(".move-list").sortable({containment: "parent", disabled: true, update: function(event, ui) {
				$.getJSON("/api/v1/move/" + ui.item.attr("data-uniq-id") + "/update/reorder/" + (ui.item.index() + 1));
			}});

			$(".capability-list").sortable({containment: "parent", disabled: true, update: function(event, ui) {
				$.getJSON("/api/v1/capability/" + ui.item.attr("data-uniq-id") + "/update/reorder/" + (ui.item.index() + 1));
			}});

			$(".type-display").click(function(e) {
				if(!editMode) return;
				e.stopPropagation();
				typeSelectorMode = 0;
				if($(this).attr("id") == "type1") activeType = 1;
				if($(this).attr("id") == "type2") activeType = 2;
				$("#type-picker").show();
				$("#type-picker").css("left", $(this).offset().left);
			});





			//---------------

			$(document).on('click', ".capability-shell", function() {
				if(editMode) return;
				$.getJSON("/api/v1/capabilities/" + $(this).attr("data-id"), function(data) {
					$(".popover .popover-title").text(data.name);
					$(".popover .popover-content").html(data.description);
				});
			});

			$(document).on('click', ".ability-row", function() {
				if(editMode) return;		
				$.getJSON("/api/v1/abilities/" + $(this).attr("data-id"), function(data) {
					$(".popover .popover-title").text(data.name);
					$(".popover .popover-content").html("<b>Frequency: </b>" + data.frequency + "<br><br>" + data.description.replace("Effect:", "<b>Effect:</b>"));
				});
			});

			$(document).on('click', ".helditem-row", function() {
				if(editMode) return;		
				$.getJSON("/api/v1/helditems/" + $(this).attr("data-id"), function(data) {
					$(".popover .popover-title").text(data.name);
					$(".popover .popover-content").html(data.description);
				});
			});

			function OptionsItem(name, description, options, method, selected) {
				var out = {"name": name, "description": description, "options": options, "selected": ko.observable(selected)};
				var sub = out.selected.subscribe(method);
				return out;
			}


			function ManagerViewModel() {
				var self = this;

				self.optionsItems = ko.observableArray([]);

				self.contestItem = new OptionsItem("Mode", "Display Pokemon moves and stats for use in combat or in contests.", ["Battle", "Contest"], function(option) {
					if(option == 0) {
						self.contestMode(false);
						localStorage.setItem('contestMode', 0);
					} else {
						self.contestMode(true);
						localStorage.setItem('contestMode', 1);
					}
				}, localStorage.getItem('contestMode') == "1" ? "1" : "0");

				self.optionsItems.push(self.contestItem);

				self.activeItem = new OptionsItem("Active", "Whether or not this Pokemon is currently in your party.", ["Inactive", "Active"], function(option) {
					if(option == 0) {
						$.getJSON("/api/v1/pokemon/" + id + "/update/active/0");
					} else {
						$.getJSON("/api/v1/pokemon/" + id + "/update/active/1");
					}
				}, "{{$pkmn->active}}");

				self.optionsItems.push(self.activeItem);

				self.legacyItem = new OptionsItem("Campaign", "The campaign that this Pokemon is a part of.", ["Foundations", "Sacrem"], function(option) {
					if(option == 0) {
						$.getJSON("/api/v1/pokemon/" + id + "/update/legacy/0");
					} else {
						$.getJSON("/api/v1/pokemon/" + id + "/update/legacy/1");
					}
				}, "{{$pkmn->legacy}}");

				self.optionsItems.push(self.legacyItem);

				@if(Auth::user()->isSpecificGM($pkmn->legacy))
				self.hiddenItem = new OptionsItem("Hidden", "Players can view the stats for this Pokemon.", ["Visible", "Hidden"], function(option) {
					if(option == 0) {
						$.getJSON("/api/v1/pokemon/" + id + "/update/hidden/0");
					} else {
						$.getJSON("/api/v1/pokemon/" + id + "/update/hidden/1");
					}
				}, "{{$pkmn->hidden}}");

				self.optionsItems.push(self.hiddenItem);

				@endif

	



				self.showOptionPanel = ko.observable(false);

				self.showNotesPanel = ko.observable(false);


				self.isEditing = ko.observable(false);

				self.id = ko.observable({{$pkmn->id}});

				self.name = ko.observable("{{$pkmn->name}}");
				self.experience = ko.observable({{$pkmn->experience}});

				self.experience.subscribe(function() {
					console.log('update!');
				})

				self.nature = ko.observable("{{$pkmn->nature()->name}}");

				self.level = ko.computed(function() {
					var $xp = self.experience();
					return Math.min(100, 1+Math.floor($xp/25)*($xp<50)+2*($xp>=50)+Math.floor(($xp-50)/50)*($xp>50)*($xp<200)+3*($xp>=200)+Math.floor(($xp-200)/200)*($xp>200)*($xp<1000)+4*($xp>=1000)+Math.floor(($xp-1000)/500)*($xp>1000)*($xp<2000)+2*($xp>=2000)
					+Math.floor(($xp-2000)/1000)*($xp>2000)*($xp<10000)+8*($xp>=10000)+Math.floor(($xp-10000)/1500)*($xp>10000)*($xp<25000)+10*($xp>=25000)+Math.floor(($xp-25000)/2500)*($xp>25000)*($xp<50000)+10*($xp>=50000)
					+Math.floor(($xp-50000)/5000)*($xp>50000)*($xp<100000)+10*($xp>=100000)+Math.floor(($xp-100000)/10000)*($xp>100000));
				});

				self.currentHealth = ko.observable({{$pkmn->current_health}});

				self.notes = ko.observable('{{str_replace("\n","\\n",$pkmn->notes)}}');

				self.owner = ko.observable("{{is_null($pkmn->trainer()) ? $pkmn->owner()->username : $pkmn->trainer()->name}}");

				self.type1 = ko.observable({{$pkmn->type1()->id}});
				self.type2 = ko.observable({{$pkmn->type2()->id}});
				
				self.baseHp = ko.observable({{$pkmn->baseStats()->hp}});
				self.baseAttack = ko.observable({{$pkmn->baseStats()->attack}});
				self.baseDefense = ko.observable({{$pkmn->baseStats()->defense}});
				self.baseSpAttack = ko.observable({{$pkmn->baseStats()->spattack}});
				self.baseSpDefense = ko.observable({{$pkmn->baseStats()->spdefense}});
				self.baseSpeed = ko.observable({{$pkmn->baseStats()->speed}});

				self.addHp = ko.observable({{$pkmn->addStats()->hp}});
				self.addAttack = ko.observable({{$pkmn->addStats()->attack}});
				self.addDefense = ko.observable({{$pkmn->addStats()->defense}});
				self.addSpAttack = ko.observable({{$pkmn->addStats()->spattack}});
				self.addSpDefense = ko.observable({{$pkmn->addStats()->spdefense}});
				self.addSpeed = ko.observable({{$pkmn->addStats()->speed}});

				self.totalStatPoints = ko.computed(function() {
					return parseInt(self.addHp()) + parseInt(self.addAttack()) + parseInt(self.addDefense()) + parseInt(self.addSpAttack()) + parseInt(self.addSpDefense()) + parseInt(self.addSpeed());
				});
				
				self.attackCombatStages = ko.observable(0);
				self.defenseCombatStages = ko.observable(0);
				self.spAttackCombatStages = ko.observable(0);
				self.spDefenseCombatStages = ko.observable(0);
				self.speedCombatStages = ko.observable(0);
				
				self.calculateCombatStageModifier = function(total, stage) {
					return stage == 0 ? total : stage < 0 ? Math.ceil(total*(1 - (Math.abs(stage)*.125))) : Math.floor(total*(1 + (stage*.25)));
				}

				self.heldItem = ko.observable("{{$pkmn->heldItem()->name}}");

				self.loyalty = ko.observable({{(Auth::user()->isSpecificGM($pkmn->legacy) || Auth::user()->hasPermissionValue('Loyalty', $pkmn->legacy)) ? $pkmn->loyalty : -9999}});

				self.gmNotes = ko.observable("{{(Auth::user()->isSpecificGM($pkmn->legacy)) ? str_replace(PHP_EOL, '\\n', $pkmn->gm_notes) : 'Hidden'}}");

				self.contestMode = ko.observable(localStorage.getItem('contestMode') == "1");

				if(self.loyalty() == -9999) $(".loyalty-row").remove();
				
				self.totalHp = ko.computed(function() { return parseInt(self.baseHp()) + parseInt(self.addHp()) });
				self.totalAttack = ko.computed(function() {
					var total = parseInt(self.baseAttack()) + parseInt(self.addAttack());
					return self.contestMode() ? Math.min(Math.floor(total/10), 6) : self.calculateCombatStageModifier(total, self.attackCombatStages());
				});
				self.totalDefense = ko.computed(function() { 
					var total = parseInt(self.baseDefense()) + parseInt(self.addDefense());
					return self.contestMode() ? Math.min(Math.floor(total/10), 6) : self.calculateCombatStageModifier(total, self.defenseCombatStages());
				});
				self.totalSpAttack = ko.computed(function() { 
					var total = parseInt(self.baseSpAttack()) + parseInt(self.addSpAttack());
					return self.contestMode() ? Math.min(Math.floor(total/10), 6) : self.calculateCombatStageModifier(total, self.spAttackCombatStages());
				});
				self.totalSpDefense = ko.computed(function() { 
					var total = parseInt(self.baseSpDefense()) + parseInt(self.addSpDefense());
					return self.contestMode() ? Math.min(Math.floor(total/10), 6) : self.calculateCombatStageModifier(total, self.spDefenseCombatStages());
				});
				self.totalSpeed = ko.computed(function() { 
					var total = parseInt(self.baseSpeed()) + parseInt(self.addSpeed());
					return self.contestMode() ? Math.min(Math.floor(total/10), 6) : self.calculateCombatStageModifier(total, self.speedCombatStages());
				});

				self.healthMod = ko.observable(0);

				self.maxHealth = ko.computed(function() {
					return self.legacyItem.selected() == 1 ? self.level() + 3 * self.totalHp() : 2 * self.level() + 4 * self.totalHp();
					
				});

				self.speedEvasion = ko.computed(function() {
					var total = parseInt(self.baseSpeed()) + parseInt(self.addSpeed());
					return Math.min(Math.min(Math.floor(total / 10), 6) + Math.min(self.speedCombatStages() > 0 ? self.speedCombatStages() : 0, 6), 9);
				});

				self.attackEvasion = ko.computed(function() {
					var total = parseInt(self.baseDefense()) + parseInt(self.addDefense());
					return self.legacyItem.selected() == 1 ?  Math.min(Math.floor(total / 5) + self.speedEvasion(), 6) + Math.min(self.defenseCombatStages() > 0 ? self.defenseCombatStages() : 0, 6) : 
					Math.min(Math.min(Math.floor(total / 5), 6) + Math.min(self.defenseCombatStages() > 0 ? self.defenseCombatStages() : 0, 6), 9);
				});

				self.specialEvasion = ko.computed(function() {
					var total = parseInt(self.baseSpDefense()) + parseInt(self.addSpDefense());
					return self.legacyItem.selected() == 1 ?  Math.min(Math.floor(total / 5)  + self.speedEvasion(), 6) + Math.min(self.spDefenseCombatStages() > 0 ? self.spDefenseCombatStages() : 0, 6) : 
					Math.min(Math.min(Math.floor(total / 5), 6) + Math.min(self.spDefenseCombatStages() > 0 ? self.spDefenseCombatStages() : 0, 6), 9);
				});

				self.stabModifier = ko.computed(function() {
					return Math.floor(self.level() / 5);
				});






				self.moveList = ko.observableArray([]);
				@foreach($pkmn->moves()->get() as $mv)
				self.moveList.push({"uniq_id": {{$mv->id}}, "move_id": {{$mv->definition()->id}}, "name": "{{$mv->definition()->name}}", "icon": "{{$mv->icon()}}", "frequency": "{{$mv->definition()->frequency}}", "ppUp" : ko.observable({{$mv->ppUp}}), "isTutor": ko.observable({{$mv->isTutor}}), "contestEffect": "{{$mv->definition()->contestEffect()->name}}", "contestType": "{{$mv->definition()->contest_type}}", "contestDice": {{$mv->definition()->contest_dice}}})
				@endforeach

				$(document).on('click', ".move-shell", function() {
					if(editMode) return;
					if(!self.contestMode()) {
						$.getJSON("/api/v1/moves/" + $(this).attr("data-id"), function(data) {
							$(".popover .popover-title").text(data.name);
							$(".popover .popover-content").html("<b>Accuracy: </b>" + data.ac + "<br><b>Range: </b>" + data.attack_range + ", " + (data.attack_type == 0 ? "Attack" : "Sp. Attack") + (data.damage != '-' ? "<br><b>Damage: </b>" + data.damage : "") + "<br><br>" + data.effects);
						});
					} else 
{						$.getJSON("/api/v1/contest/moves/" + $(this).attr("data-id"), function(data) {
							$(".popover .popover-title").text(data.name);
							$(".popover .popover-content").html("<b>Type: </b>" + data.contest_type + "<br><b>Dice: </b>" + data.contest_dice + "d6<br><br>" + data.contest_ability_name + " - " + data.contest_ability_desc);
						});

					}
				});

				$(document).click(function(ev) {
					if(ev.target.id != "type-picker") $("#type-picker").hide();
					console.log($(ev.target).parents("editPanel").length);
					if($(ev.target).parents("#editPanel").length == 0 && $(ev.target).parents("#editGear").length == 0) self.showOptionPanel(false);
					if($(ev.target).parents("#notePanel").length == 0&& $(ev.target).parents("#editNotes").length == 0) self.showNotesPanel(false);
				});

				$("#trainer-give-submit").click(function() {
					$.getJSON("/api/v1/pokemon/" + self.id() + "/give/" + $("#trainer-give-input").val(), function() {
						window.location.reload();
					});
				});


				$(".notes-box").blur(function() {
					$.post('/api/v1/pokemon/' + self.id() + '/update/notes', { value: self.notes()}, function() {
						console.log('Notes successfully updated.');
					});
				});
		

				$(".type-pick-item").click(function() {
					switch(typeSelectorMode) {
						case 0:
							var pos = 1;
							if(activeType == 1) {
								$elem = $("#type1");
							} else if(activeType == 2) {
								$elem = $("#type2");
								pos = 2;
							} else {
								$("#type-picker").hide();
								return;
							}
							var t_id = $(this).attr("data-id");
							$elem.attr("data-id", t_id);
							$elem.attr("src", $(this).attr("src"));
							$.getJSON("/api/v1/pokemon/" + id + "/update/type/" + pos + "/" + t_id);
							$("#type-picker").hide();
							if(pos == 1) self.type1(parseInt(t_id));
							if(pos == 2) self.type2(parseInt(t_id));
							break;
						case 1:
							var m_id = typeSelectorElem.attr("data-uniq-id");
							var t_id = $(this).attr("data-id");
							$.getJSON("/api/v1/move/" + m_id + "/update/type/" + t_id, function(data) {
								typeSelectorElem.find(".move-type").attr("src", data.icon);
							});
							$("#type-picker").hide();
						break;
					}
				});

				$(document).on("click", ".move-type", function(e) {
					if(!editMode) return;
					e.stopPropagation();
					typeSelectorMode = 1;
					typeSelectorElem = $(this).parents(".move-shell");
					$("#type-picker").show();
					$("#type-picker").css("left", $(this).offset().left);
				}); 

	

				$(document).on("typeahead:closed", "#nature-input", function() {
					$.getJSON("/api/v1/pokemon/" + id + "/update/nature/" + $(this).typeahead('val'));
					self.nature($(this).typeahead('val')[0]);
				});

				$(document).on("typeahead:closed", "#helditem-input", function() {
					$.getJSON("/api/v1/pokemon/" + id + "/update/helditem/" + $(this).typeahead('val'), function(data) {
						$(".helditem-row").attr("data-id", data.id);
					});
					self.heldItem($(this).typeahead('val')[0]);
				});


				$(document).on("typeahead:closed", "#speciesEdit", function() {
					var source = this;
					if($(this).typeahead("val")[0] != undefined) {
						$.getJSON("/api/v1/pokemon/" + id + "/update/species/" + $(this).typeahead("val")[0].replace(' ', '-'), function(data) {
							$("#pkmn-anim").attr("src", data.animation);
						});
					}
				});

				$(document).on("click", "#submit-gm-notes", function() {
					$.getJSON("/api/v1/pokemon/" + id + "/update/gmnotes/" + encodeURIComponent(self.gmNotes()));
				});


				self.toggleOptionsPanel = function() {
					self.showOptionPanel(!self.showOptionPanel());
				}

				self.toggleNotesPanel = function() {
					self.showNotesPanel(!self.showNotesPanel());
				}

				self.isOverAllocated = ko.computed(function() {
					return self.totalStatPoints() > self.level() - 1;
				});

				self.isUnderAllocated = ko.computed(function() {
					return self.totalStatPoints() < self.level() - 1;
				});

				self.remainingStatPoints = ko.computed(function() {
					return self.level() - 1 - self.totalStatPoints();
				});

				self.showNotificationAlert = ko.computed(function() {
					return self.isOverAllocated() || self.isUnderAllocated();
				});
				
				self.displayFrequency = function(row) {
				console.log(row);
					if(row.ppUp()) {
						switch (row.frequency) {
							case 'At-Will': return 'At-Will';
							case 'EOT': return 'At-Will';
							case 'Battle': return 'EOT';
							case 'Center': return 'Battle';
							default: return 'Undefined';
						}
					}
					return row.frequency;
				}
				
				self.decreaseHealth = function() {
					var mod = parseInt(self.healthMod());
					var fin = self.currentHealth() - mod
					self.currentHealth(fin);
					$.getJSON("/api/v1/pokemon/" + id + "/update/health/" + self.currentHealth());
				}

				self.increaseHealth = function() {
					var mod = parseInt(self.healthMod());
					var fin = self.currentHealth() + mod
					self.currentHealth(fin);
					$.getJSON("/api/v1/pokemon/" + id + "/update/health/" + self.currentHealth());
				}
				
				self.increaseCombatStage = function(stage) {
					stage(stage() + 1);
					if(stage() > 6) stage(6);
				}
				
				self.decreaseCombatStage = function(stage) {
					stage(stage() - 1);
					if(stage() < -6) stage(-6);
				}

				self.changeTutorStatus = function(row) {
				if(!editMode) return;
					if(row.isTutor()) {
						row.isTutor(false);
						$.getJSON("/api/v1/move/" + row.uniq_id + "/update/tutor/0");
					} else {
						row.isTutor(true);
						$.getJSON("/api/v1/move/" + row.uniq_id + "/update/tutor/1");
					}
				}
				
				self.changePPUpStatus = function(row) {
				if(!editMode) return;
					if(row.ppUp()) {
						row.ppUp(false);
						$.getJSON("/api/v1/move/" + row.uniq_id + "/update/ppup/0");
					} else {
						row.ppUp(true);
						$.getJSON("/api/v1/move/" + row.uniq_id + "/update/ppup/1");
					}
				}
				
				$(document).on("typeahead:closed", ".move-input", function() {
					var source = this;
					if($(this).typeahead("val")[0] != undefined) {
						$.getJSON("/api/v1/pokemon/" + id + "/insert/move/" + $(this).typeahead("val")[0].replace(' ', '-'), function(data) {
							self.moveList.push({"uniq_id":data.uniq_id, "move_id": data.id, "name": data.name, "icon": "http://cdn.acceptableice.com/pkmn/type-badges/" + data.type + ".png", "frequency": data.frequency, "ppUp": ko.observable(false),
										"isTutor": ko.observable(false), "contestEffect": data.contest_effect, "contestType": data.contest_type, "contestDice": data.contest_dice})


							$(source).parents(".move-shell").remove();
						}).fail(function(error) {
							$(source).parents(".move-shell").remove();

						});
					} else {
						$(this).parents(".move-shell").remove();
					}
				});
			}

 			var viewmodel = new ManagerViewModel();
			ko.applyBindings(viewmodel);

		});
	</script>
@stop
@section('content')
<? $isGM = Auth::user()->isSpecificGM($pkmn->legacy); ?>
	<div id="type-picker">
		@foreach(Type::All() as $t)
			<img class="type-badge type-pick-item" data-id="{{$t->id}}" src="{{$t->icon()}}"> 
		@endforeach
	</div>
	@if($pkmn->user_id == Auth::user()->id || $isGM) <div class="edit-button">Edit</div> @endif
	<div class="popover">
		<div class="popover-title"></div>
		<div class="popover-content"></div>
	</div>
	@if($isGM)
		<div class="gm-options">
			<div class="popover-title">GM Options</div>
			<div class="popover-content">
				Give to Trainer: <input class="stat-input stat-input-small" id="trainer-give-input"> <button id="trainer-give-submit" class="submit" style="height: 25px; line-height: 12px">Go</button>
				Notes:
				<textarea id="gm-notes" data-bind="value: $root.gmNotes"></textarea>
				<button class="submit" id="submit-gm-notes">Save</button>
			</div>

		</div>
	@endif
	<div class="edit-panel hideOnLoad" id="notePanel" data-bind="visible: $root.showNotesPanel, css: {'hideOnLoad': false}">
		<div class="edit-panel-content">
			<textarea class="notes-box" data-bind="value: $root.notes"></textarea>
		</div>
	</div>
	<div class="edit-panel hideOnLoad" id="editPanel" data-bind="visible: $root.showOptionPanel, css: {'hideOnLoad': false}">
		<div class="edit-panel-overlay"></div>
		<div class="edit-panel-content">
			<div class="edit-panel-list">
				<div class="edit-option error-option" data-bind="visible: $root.isOverAllocated">
					<div class="edit-option-title"><i class="fa fa-warning"></i> Over Stat Point Budget</div>
					<div class="edit-option-desc">You are <b data-bind="text: -1*$root.remainingStatPoints() + (($root.remainingStatPoints() == -1)  ? ' stat point' : ' stat points')"></b> over limit.</div>
				</div>
				<div class="edit-option success-option" data-bind="visible: $root.isUnderAllocated">
					<div class="edit-option-title"><i class="fa fa-check-circle-o"></i> Under Stat Point Budget</div>
					<div class="edit-option-desc">You have <b data-bind="text: $root.remainingStatPoints() + (($root.remainingStatPoints() == 1)  ? ' stat point' : ' stat points')"></b> left to spend.</div>
				</div>
				<!-- ko foreach: optionsItems -->
				<div class="edit-option">
					<div class="edit-option-title" data-bind="text: name"></div>
					<div class="edit-option-desc" data-bind="text: description"></div>
					<div class="switch-toggle switch-candy">
						<!-- ko foreach: options -->
						<input type="radio" data-bind="checked: $parent.selected, attr: {'id': $data, 'value': $index()}">
						<label onclick="" data-bind="text: $data, attr: {'for': $data}" data-bind="click: $parent.method"></label>
						<!-- /ko -->
						<a></a>
					</div>
				</div>
				<!-- /ko -->


			</div>
		</div>
	</div>
	<div class="battle-bg">		
		<img id="pkmn-anim" src="{{$pkmn->species()->animatedSprite()}}">
	</div>
	<div class="left-set">
		<div class="pkmn-name name-row">
			<div class="display-row">
				<span class="name" data-bind="text: $root.name()"></span><span class="level" data-bind="text: 'Level ' + $root.level()"> </span><div class="substat" data-bind="text: $root.experience() + ' XP'"></div>
				<div class="edit-gear" id="editGear" data-bind="click: $root.toggleOptionsPanel"><i class="fa fa-cog"></i></div>
				<div class="edit-notes" id="editNotes" data-bind="click: $root.toggleNotesPanel"><i class="fa fa-file-text"></i></div>
				<div class="notification-alert hideOnLoad" data-bind="visible: $root.showNotificationAlert, css: {'hideOnLoad': false}"><i class="fa fa-exclamation"></i></div>
			</div>
			<div class="edit-row">
				<input class='stat-input name-input' id='nameEdit' data-bind="value: $root.name()">
				<div class='species-input-shell'><input class='stat-input species-input' id='speciesEdit' value="{{$pkmn->species()->name}}"></div>
				<span class="level edit-level" data-bind="text: 'Level ' + $root.level()"></span>
				<div class="substat"><input class='stat-input xp-input' id="setXP" data-bind="value: $root.experience"> XP</div>
			</div>
		</div>
		<div class="stat-row"><div class="row-title">Owner</div> <div class="row-content" data-bind="text: $root.owner()"></div></div>
		<div class="stat-row"><div class="row-title">Type</div> 
			<div class="row-content">
				<img class="type-badge type-display" id="type1" data-id="{{$pkmn->type1()->id}}" src="{{$pkmn->type1()->icon()}}" data-bind="visible: $root.type1() != 0 || $root.isEditing()">
				<img class="type-badge type-display" id="type2" data-id="{{$pkmn->type2()->id}}" src="{{$pkmn->type2()->icon()}}" data-bind="visible: $root.type2() != 0 || $root.isEditing()">
			</div>
		</div>
		<div class="stat-row loyalty-row"><div class="row-title">Loyalty</div> 
			<div class="row-content display-row" data-bind="text: $root.loyalty()"></div>
			<div class="row-content edit-row">
				<input class='stat-input stat-input-small' id='loyalty-edit' data-bind="value: $root.loyalty" style="margin-top: 5px;">
			</div>
		</div>		
		<div id="health-row" class="stat-row health-row" data-hp="{{$pkmn->maxHealth()}}" data-base="{{$pkmn->baseStats()->hp}}" data-add="{{$pkmn->addStats()->hp}}">
			<div class="row-title">HP</div> 
			<div class="row-content display-row">
				<span class="health-display" data-bind="text: $root.currentHealth() + '/' + $root.maxHealth()"></span> <div class="substat" data-bind="text: '(' + $root.baseHp() + ' + ' + $root.addHp() + ')'"></div>
				<div class="mod-stage mod-health-stage">
					<div class="stage-action minus" data-bind="click: $root.decreaseHealth">&minus;</div>
					<input class="stat-input stat-input-small stage-input" id="modHealth" data-bind="value: $root.healthMod">
					<div class="stage-action plus" data-bind="click: $root.increaseHealth">&plus;</div>
				</div>
			</div>
			<div class="row-content edit-row">
				<input class='stat-input stat-input-small' id='hp-base' data-bind="value: $root.baseHp"> +
				<input class='stat-input stat-input-small' id='hp-add' data-bind="value: $root.addHp">
				
			</div>
		</div>

		<div id="attack-row" class="stat-row" data-base="{{$pkmn->baseStats()->attack}}" data-add="{{$pkmn->addStats()->attack}}">
			<div class="row-title" data-bind='text: $root.contestMode() ? "Cool" : "Attack"'></div> 
			<div class="row-content display-row">
				<span class="mainstat" data-bind="text: $root.totalAttack()"></span><div class="substat" data-bind="text: '(' + $root.baseAttack() + ' + ' + $root.addAttack() + ')'"></div>
				<div class="mod-stage">
					<div class="stage-action minus" data-bind="click: $root.decreaseCombatStage($root.attackCombatStages)">&minus;</div>
					<div class="stat-input stat-input-small stage-disp" data-bind="text: $root.attackCombatStages"></div>
					<div class="stage-action plus" data-bind="click: $root.increaseCombatStage($root.attackCombatStages)">&plus;</div>
					<div class="combat-stage-text">Combat Stages</div>
				</div>
			</div>
			<div class="row-content edit-row">
				<input class='stat-input stat-input-small' id='attack-base' data-bind="value: $root.baseAttack"> +
				<input class='stat-input stat-input-small' id='attack-add' data-bind="value: $root.addAttack">
			</div>
		</div>
		<div id="defense-row" class="stat-row" data-base="{{$pkmn->baseStats()->defense}}" data-add="{{$pkmn->addStats()->defense}}">
			<div class="row-title" data-bind='text: $root.contestMode() ? "Tough" : "Defense"'></div> 
			<div class="row-content display-row">
				<span class="mainstat" data-bind="text: $root.totalDefense()"></span><div class="substat" data-bind="text: '(' + $root.baseDefense() + ' + ' + $root.addDefense() + ')'"></div>
				<div class="mod-stage">
					<div class="stage-action minus" data-bind="click: $root.decreaseCombatStage($root.defenseCombatStages)">&minus;</div>
					<div class="stat-input stat-input-small stage-disp" data-bind="text: $root.defenseCombatStages"></div>
					<div class="stage-action plus" data-bind="click: $root.increaseCombatStage($root.defenseCombatStages)">&plus;</div>
					<div class="combat-stage-text">Combat Stages</div>
				</div>
			</div>
			<div class="row-content edit-row">
				<input class='stat-input stat-input-small' id='defense-base' data-bind="value: $root.baseDefense"> +
				<input class='stat-input stat-input-small' id='defense-add' data-bind="value: $root.addDefense">
			</div>
		</div>
		<div id="spattack-row" class="stat-row" data-base="{{$pkmn->baseStats()->spattack}}" data-add="{{$pkmn->addStats()->spattack}}">
			<div class="row-title" data-bind='text: $root.contestMode() ? "Beauty" : "Sp. Attack"'></div> 
			<div class="row-content display-row">
				<span class="mainstat" data-bind="text: $root.totalSpAttack()"></span><div class="substat" data-bind="text: '(' + $root.baseSpAttack() + ' + ' + $root.addSpAttack() + ')'"></div>
				<div class="mod-stage">
					<div class="stage-action minus" data-bind="click: $root.decreaseCombatStage($root.spAttackCombatStages)">&minus;</div>
					<div class="stat-input stat-input-small stage-disp" data-bind="text: $root.spAttackCombatStages"></div>
					<div class="stage-action plus" data-bind="click: $root.increaseCombatStage($root.spAttackCombatStages)">&plus;</div>
					<div class="combat-stage-text">Combat Stages</div>
				</div>
			</div>
			<div class="row-content edit-row">
				<input class='stat-input stat-input-small' id='spattack-base' data-bind="value: $root.baseSpAttack"> +
				<input class='stat-input stat-input-small' id='spattack-add' data-bind="value: $root.addSpAttack">
			</div>
		</div>
		<div id="spdefense-row" class="stat-row" data-base="{{$pkmn->baseStats()->spdefense}}" data-add="{{$pkmn->addStats()->spdefense}}">
			<div class="row-title" data-bind='text: $root.contestMode() ? "Smart" : "Sp. Defense"'></div> 
			<div class="row-content display-row">
				<span class="mainstat" data-bind="text: $root.totalSpDefense()"></span><div class="substat" data-bind="text: '(' + $root.baseSpDefense() + ' + ' + $root.addSpDefense() + ')'"></div>
				<div class="mod-stage">
					<div class="stage-action minus" data-bind="click: $root.decreaseCombatStage($root.spDefenseCombatStages)">&minus;</div>
					<div class="stat-input stat-input-small stage-disp" data-bind="text: $root.spDefenseCombatStages"></div>
					<div class="stage-action plus" data-bind="click: $root.increaseCombatStage($root.spDefenseCombatStages)">&plus;</div>
					<div class="combat-stage-text">Combat Stages</div>
				</div>
			</div>
			<div class="row-content edit-row">
				<input class='stat-input stat-input-small' id='spdefense-base' data-bind="value: $root.baseSpDefense"> +
				<input class='stat-input stat-input-small' id='spdefense-add' data-bind="value: $root.addSpDefense">
			</div>
		</div>
		<div id="speed-row" class="stat-row" data-base="{{$pkmn->baseStats()->speed}}" data-add="{{$pkmn->addStats()->speed}}">
			<div class="row-title" data-bind='text: $root.contestMode() ? "Cute" : "Speed"'></div> 
			<div class="row-content display-row">
				<span class="mainstat" data-bind="text: $root.totalSpeed()"></span><div class="substat" data-bind="text: '(' + $root.baseSpeed() + ' + ' + $root.addSpeed() + ')'"></div>
				<div class="mod-stage">
					<div class="stage-action minus" data-bind="click: $root.decreaseCombatStage($root.speedCombatStages)">&minus;</div>
					<div class="stat-input stat-input-small stage-disp" data-bind="text: $root.speedCombatStages"></div>
					<div class="stage-action plus" data-bind="click: $root.increaseCombatStage($root.speedCombatStages)">&plus;</div>
					<div class="combat-stage-text">Combat Stages</div>
				</div>
			</div>
			<div class="row-content edit-row">
				<input class='stat-input stat-input-small' id='speed-base' data-bind="value: $root.baseSpeed"> +
				<input class='stat-input stat-input-small' id='speed-add' data-bind="value: $root.addSpeed">
			</div>
		</div>

		<div class="stat-row nature-row"><div class="row-title">Nature</div> 
			<div class="display-row"><div class="row-content" data-bind="text: $root.nature()"></div></div>
			<div class="edit-row"><input class='stat-input nature-input' id='nature-input' value="{{$pkmn->nature()->name}}"></div>
		</div>
		@foreach($pkmn->abilities()->get() as $av)
			<div class="stat-row ability-row" data-id="{{$av->definition()->id}}"><div class="row-title">Ability</div> <div class="row-content">{{ $av->definition()->name }}</div></div>
		@endforeach
		<div class="stat-row helditem-row" data-id="{{$pkmn->heldItem()->id}}"><div class="row-title">Held Item</div> 
			<div class="display-row"><div class="row-content" data-bind="text: $root.heldItem()"></div></div>
			<div class="edit-row"><input class='stat-input nature-input helditem-input' id='helditem-input' value="{{$pkmn->heldItem()->name}}"></div>
		</div>
		<div class="stat-row" data-bind="visible: !$root.contestMode()"><div class="row-title">Attack Evasion</div><div class="row-content" data-bind="text: $root.attackEvasion()"></div></div>
		<div class="stat-row" data-bind="visible: !$root.contestMode()"><div class="row-title">Special Evasion</div><div class="row-content" data-bind="text: $root.specialEvasion()"></div></div>
		<div class="stat-row" data-bind="visible: !$root.contestMode()"><div class="row-title">Speed Evasion</div><div class="row-content" data-bind="text: $root.speedEvasion()"></div></div>
		<div class="stat-row" data-bind="visible: !$root.contestMode()"><div class="row-title">STAB Modifier</div><div class="row-content" data-bind="text: $root.stabModifier()"></div></div>
		<div class="stat-row effectiveness-row" data-bind="visible: !$root.contestMode()">
			<div class="row-title">Defensive Effectiveness</div>
			<?php $defensive = $pkmn->getCombinedDefensiveTypeEffectivenessStrings(); ?>
			<div class="effectiveness-subrow">
				<div class="subrow-title">Weak to:</div>
				@foreach($defensive["SE"] as $k => $v)
					<div class="effectiveness-element {{strtolower($k)}}"><div class="effectiveness-text">{{$k}}</div> <div class="effectiveness-value">&times;{{Type::getFractionSigns($v)}}</div></div>
				@endforeach
				<div class="subrow-title">Resistant to:</div>
				@foreach($defensive["NVE"] as $k => $v)
					<div class="effectiveness-element {{strtolower($k)}}"><div class="effectiveness-text">{{$k}}</div> <div class="effectiveness-value">&times;{{Type::getFractionSigns($v)}}</div></div>
				@endforeach
				<div class="subrow-title">Immune to:</div>
				@foreach($defensive["Immune"] as $k => $v)
					<div class="effectiveness-element {{strtolower($k)}}"><div class="effectiveness-text">{{$k}}</div> <div class="effectiveness-value">&times;{{Type::getFractionSigns($v)}}</div></div>
				@endforeach
			</div>

		</div>
	</div>
	<div class="right-set">
		<div class="move-list" data-bind="foreach: $root.moveList">
			<div class="move-shell combat-move-shell" data-bind="attr: {'data-uniq-id': uniq_id, 'data-id': move_id}">
				<div class="move-shell-inner move-combat-shell" data-bind="visible: !$root.contestMode()">
					<div class="move-name" data-bind="text: name"></div>
					<img class="move-type" data-bind="attr: {'src': icon}">
					<div class="move-frequency" data-bind="text: $root.displayFrequency($data), click: $root.changePPUpStatus"></div>
					<div class="move-tutor" data-bind="text: isTutor() ? 'Tutor Move' : 'Standard Move', click: $root.changeTutorStatus"></div>
				</div>
				<div class="move-shell-inner move-contest-shell hideOnLoad" data-bind="visible: $root.contestMode(), css: {'hideOnLoad': false}">
					<div class="move-name" data-bind="text: name"></div>
					<div class="move-type" data-bind="text: contestEffect"></div>
					<div class="move-frequency" data-bind="text: contestType"></div>
					<div class="contest-dice" data-bind="text: contestDice  == 0 ? '--' : contestDice + 'd6'"></div>
				</div>
			</div>
		</div>
		<div class="capability-list" data-bind="visible: !$root.contestMode()">
		@foreach($pkmn->capabilities()->get() as $cv)
			<div class="capability-shell" data-uniq-id="{{$cv->id}}" data-id="{{$cv->definition()->id}}"><div class="capability-shell-inner">
				<div class="capability-name">{{ $cv->definition()->name }} {{ $cv->value == 0 ? "" : $cv->value }}</div>
			</div></div>
		@endforeach
		</div>
	</div>

@stop