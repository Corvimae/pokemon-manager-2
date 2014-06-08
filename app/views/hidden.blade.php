@extends('layout')
@section('title', 'Pokemon Viewer')
@section('script')
	<script src="/js/jquery-ui-1.10.4.custom.min.js"></script>
	<script type="text/javascript">
	
	</script>
@stop
@section('content')
	<div class="header-main">This Pokemon is Hidden</div>
	<div class="hidden-desc">{{$pkmn->name}} has been marked as hidden, and can only be viewed by a GM.</div>
	<div class="hidden-info">If you believe you should have access to this Pokemon's sheet, contact a GM.</div>

@stop