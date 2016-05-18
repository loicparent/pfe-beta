@extends( 'layout' )
@section( 'content' )
@section( 'title', $title )

<div class="blockTitle">
	<h2 class="mainTitle">{{ $title }}</h2>
</div>

@if ( count($students) !== 0 )
	<ul class="panel-body">
		@foreach( $students as $student )
			<li>
				<a href="{{ action( 'PageController@viewUser', [ 'id' => $student[0]->id ] ) }}">{{ $student[0]->firstname }} {{ $student[0]->name }}</a>
				<a href="mailto:{{ $student[0]->email}}">Contactez {{ $student[0]->firstname }}</a>
		@endforeach
	</ul>
@else
	<p>Il n’y a aucun étudiant pour le moment</p>
@endif

@stop