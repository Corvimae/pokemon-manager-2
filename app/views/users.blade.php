@extends('layout')

@section('content')
	@foreach($users as $user)
		<p>{{ $user->moves()->first()->definition() }}</p>
	@endforeach
@stop