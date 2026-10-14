@extends('layouts.app')
@section('title', 'Shared worlds')

@section('content')
<div class="section-head">
    <h1>Shared worlds</h1>
    @auth<a href="{{ route('games.create') }}" class="btn">Share a world</a>@else<a href="{{ route('login') }}" class="btn btn-ghost">Log in to share</a>@endauth
</div>
<p class="lead">Saved worlds uploaded by players. Download one, drop it into your Blockworld folder as world.sav, and press C on the menu to continue it.</p>

<form class="filters" method="get">
    <select name="mode" onchange="this.form.submit()">
        <option value="">All modes</option>
        @foreach (\App\Models\SharedGame::MODES as $k => $v)<option value="{{ $k }}" @selected($mode === $k)>{{ $v }}</option>@endforeach
    </select>
    <select name="sort" onchange="this.form.submit()">
        <option value="new" @selected($sort === 'new')>Newest</option>
        <option value="popular" @selected($sort === 'popular')>Most downloaded</option>
        <option value="score" @selected($sort === 'score')>Highest score</option>
    </select>
</form>

@if ($games->isEmpty())
    <p class="muted">Nothing here yet.</p>
@else
    <div class="grid cards">@each('games._card', $games, 'game')</div>
    {{ $games->links() }}
@endif
@endsection
