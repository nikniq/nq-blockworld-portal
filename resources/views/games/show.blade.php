@extends('layouts.app')
@section('title', $game->title)
@section('description', Str::limit($game->description ?: 'A shared Blockworld world by '.$game->user->name, 150))

@section('content')
<article class="game-page">
    @if ($game->screenshot_path)<img src="{{ Storage::url($game->screenshot_path) }}" alt="Screenshot of {{ $game->title }}" class="hero-shot">@endif
    <h1>{{ $game->title }}</h1>
    <p class="muted">Shared by <a href="{{ route('profile.show', $game->user) }}">{{ $game->user->name }}</a> {{ $game->created_at->diffForHumans() }}</p>
    <p class="stats">
        <span>Mode <strong>{{ $game->modeLabel() }}</strong></span>
        <span>World <strong>{{ $game->sizeLabel() }}</strong></span>
        <span>Score <strong>{{ number_format($game->score) }}</strong></span>
        <span>Nights <strong>{{ $game->nights }}</strong></span>
        <span>Downloads <strong>{{ number_format($game->downloads) }}</strong></span>
    </p>
    @if ($game->description)<div class="prose">{!! nl2br(e($game->description)) !!}</div>@endif

    <div class="cta">
        <a href="{{ route('games.download', $game) }}" class="btn btn-big">Download world.sav ({{ $game->humanSize() }})</a>
        @can('update', $game)
            <a href="{{ route('games.edit', $game) }}" class="btn btn-ghost">Edit</a>
            <form method="post" action="{{ route('games.destroy', $game) }}" class="inline" onsubmit="return confirm('Remove this world?')">@csrf @method('DELETE')<button class="btn btn-danger">Remove</button></form>
        @endcan
    </div>
    <p class="small muted">To play it, quit Blockworld, replace world.sav in your Blockworld data folder with this file, then press C on the main menu. Keep a copy of your own save first.</p>
</article>
@endsection
