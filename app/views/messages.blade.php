@extends('layout')
@section('title', 'Pokemon Manager 2')
@section('script')
<script type="text/javascript">
$(function() {
	function messageVM() {
		var self = this;
		
		self.messages = ko.observableArray([]);
		<? $messages = $showUnread ? Auth::user()->newMessages()->get() : 
									 Auth::user()->messages()->get() ?>
		@foreach($messages as $msg)
			self.messages.push({"id": {{$msg->id}}, "from": "{{$msg->from()}}", "subject": "{{addslashes($msg->subject)}}", "content": "{{addslashes($msg->content)}}", "date": "{{$msg->created_at->diffForHumans()}}", "seen": ko.observable({{$msg->seen()}})});
		@endforeach
		
		self.activeMessage = ko.observable();

		self.showAllMessages = function() {
			document.location = "/messages"
		}

		self.showUnreadMessages = function() {
			document.location = "/messages/unread"
		}

		self.getMessagePreview = function(data) {
			return data.content.replace(/(<([^>]+)>)/ig,"");
		}

		self.setActiveMessage = function(data) {
			self.activeMessage(data);

			if(!data.seen()) {
				$.getJSON('/api/v1/messages/seen/' + data.id, function(returnval) {
					data.seen(1);
				});
			}
		}
	}
	
	ko.applyBindings(new messageVM());
});
</script>
@stop
@section('content')
<div class="pkmn-name message-h1"><div class="user-title">{{$showUnread ? 'Unread ' : ''}}Messages</div></div>
<div class="sidebar">
	<div class="stat-row" data-bind="click: $root.showAllMessages"><div class="row-title">All Messages</div></div>
	<div class="stat-row" data-bind="click: $root.showUnreadMessages"><div class="row-title">Unread Messages</div></div>

</div>
<table class='message-table-header'>
	<thead>
		<tr>
			<th class="message-title">Subject</th>
			<th class="message-from">From</th>
			<th class="message-date">Date</th>
		</tr>
	</thead>
</table>
<div class="mainbar">
	<table class="message-table" >
	<tbody data-bind="foreach: $root.messages">
		<tr class="message-shell" data-bind="click: function() { $root.setActiveMessage($data); }">
			<td class="message-title" data-bind="html: seen() ? subject : '<b>' + subject + '</b>'"></div>
			<td class="message-from" data-bind="text: from"></div>
			<td class="message-date" data-bind="text: date"></div>
		</tr>
	</tbody>
	</table>
</div>

<div class="view-message" data-bind="with: activeMessage">
	<div class="view-message-header">
		<div class="view-message-header-row"><div class="message-field">Subject</div><div class="view-message-title" data-bind="text: subject"></div></div>
		<div class="view-message-header-row"><div class="message-field">From</div><div class="view-message-from" data-bind="text: from"></div></div>
		<div class="view-message-header-row"><div class="message-field">Date</div><div class="view-message-date" data-bind="text: date"></div></div>
	</div>
		<div class="message-content" data-bind="html: content"></div>
</div>


@stop