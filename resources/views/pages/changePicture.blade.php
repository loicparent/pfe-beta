@extends('layout')
@section('title', $title)
@section('content')

	<div class="blockTitle">
		<h2 class="mainTitle">{{ $title }}</h2>
		<a title="Revenir à la page de profil" class="backButton blockTitle__backButton unlink mainColorfont" href="{!! action( 'PageController@about' ) !!}"><span class="hidden">Revenir à la page précédente</span><span class="icon-arrow-left"></span></a>
	</div>

	<div class="box--group">
		<!-- Profil photo -->
		<div class="box_profilPicture box__profilImage box__profilImage--profilPage box__profilImage--profilPage--change">
			<img class="box__profilImage" src="{{ url() }}/img/profilPicture/{{ Auth::user()->image }}" alt="Image de profil">
		</div>
		<!-- id information -->
		<div class="box box--shadow box_profil--picture">
			<form class="box__group--content" action="" method="post" enctype="multipart/form-data">
				<div class="form-group">
					<input type="hidden" name="_token" value="{{ csrf_token() }}">
					<label for="image">Changer l’image</label>
					<input type="file" class="form-control" name="image" id="image">
				</div>

				<div class="form-group text-center">
					<a href="{{ action( 'PageController@about' ) }}" class="btn btn-back">Annuler</a>
					<input type="submit" class="btn btn-send" value="Valider les modifications">
					<div class="clear"></div>
				</div>

				@include('errors.profilError')
			</form>
		</div>
	</div>
@endsection