@extends('layouts.app')
@section('title', 'Players')

@section('content')
<h1>Players</h1>
<p class="lead">Everyone with a profile, ranked by their best score.</p>
<div class="grid cards players">
    @forelse ($players as $player)
        <a href="{{ route('profile.show', $player) }}" class="card player">
            @include('profile._avatar', ['user' => $player])
            <div>
                <strong>{{ $player->name }}</strong> <span class="muted">&#64;{{ $player->handle }}</span>
                <div class="small muted">Best score {{ number_format($player->best_score) }} · {{ $player->games_count }} {{ Str::plural('world', $player->games_count) }}@if($player->platform) · {{ \App\Models\User::PLATFORMS[$player->platform] }}@endif</div>
            </div>
        </a>
    @empty
        <p class="muted">No players yet. <a href="{{ route('register') }}">Be the first.</a></p>
    @endforelse
</div>
{{ $players->links() }}
@endsection
