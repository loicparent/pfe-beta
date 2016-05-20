@extends( 'layout' )
	@section('title', $title)
    @section( 'content' )
        <div class="blockTitle">
            <h2 class="mainTitle">Rechercher un cours</h2>
            <a class="backButton blockTitle__backButton unlink mainColorfont" href="{!! action( 'CourseController@index' ) !!}"><span class="hidden">Revenir à la page précédente</span><span class="icon-arrow-left"></span></a>
        </div>

        <div class="box--group">
            <div class="box box--shadow">
                <form class="box__group--content searchForm" action="" method="post">
                    {{--<p>Retrouver un cours grâce à son intitulé, le nom du professeur, du groupe, de l'école ou encore de la ville ou bien, en utilisant le code d'accès du cours fourni par le professeur</p>--}}
                    <div class="form-group form-group--radioSearch">
                        <input type="radio" class="searchForm__radio hidden" name="type" value="search" id="type-1" checked> <label for="type-1">Recherche</label>
                        <input type="radio" class="searchForm__radio hidden" name="type" value="access" id="type-2"> <label for="type-2">Code d'accès</label>
                    </div>
                    <div class="form-group form-group--nospace">
                        <label class="hidden" for="search">Rechercher un cours</label>
                        <input type="text" name="search" id="search" placeholder="Rechercher un cours" autofocus>
                        <input type="submit" value="Envoyer" class="hidden">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </div>
                </form>
            </div>

            @include('errors.profilError')

            <ul>
                @if( $courses )
                    @foreach ($courses as $course)
                    <li class="box box--shadow box__group--list box__list--results">
                        <a class="unlink blockLink box__group--list--list box__group--list--results" href="{!! action( 'CourseController@view', [ 'id' => $course->id ] ) !!}">
                            <div class="box__group--list--results--title">
                                <span class="icon icon-book-open mainColorfont"></span>
                                <span class="mainColorfont">Cours </span>
                            </div>
                            <span class="box__group--list--content">{{ $course->title }}</span>
                        </a>
                        <div class="box__group--list--list box__group--list--results">
                            <div class="box__group--list--results--title">
                                <span class="icon icon-users mainColorfont"></span>
                                <span class="mainColorfont">Groupe </span>
                            </div>
                            <span class="box__group--list--content">{{ $course->group }}</span>
                        </div>

                        @foreach( $users as $user )
                            @if( $course->teacher_id == $user->id )
                                <a class="unlink blockLink box__group--list--list box__group--list--results" href="{!! action( 'PageController@viewUser', [ 'id' => $user->id ] ) !!}">
                                    <div class="box__group--list--results--title">
                                        <span class="icon icon-user mainColorfont"></span>
                                        <span class="mainColorfont">Prof </span>
                                    </div>
                                    <span class="box__group--list--content">{{ $user->firstname }} {{ $user->name }}</span>
                                </a>
                            @endif
                        @endforeach

                    <div class="box__group--list--list box__group--list--results">
                        <div class="box__group--list--results--title">
                            <span class="icon icon-home mainColorfont"></span>
                            <span class="mainColorfont">École </span>
                        </div>
                        <span class="box__group--list--content">{{ $course->school }}</span>
                    </div>
                    <div class="box__group--list--list box__group--list--results">
                        <div class="box__group--list--results--title">
                            <span class="icon icon-pointer mainColorfont"></span>
                            <span class="mainColorfont">Ville </span>
                        </div>
                        <span class="box__group--list--content">{{ $course->place }}</span>
                    </div>
                </li>
                @endforeach
            @endif
                @if( empty( $courses[0] ) )
                        <li class="item--null">Aucun résultat pour le moment</li>
                @endif
            </ul>
        </div>
    @stop