@extends('layouts.app')
@section('title', $user->name)

@section('content')
<section class="profile-head">
    @include('profile._avatar', ['user' => $user, 'size' => 'big'])
    <div>
        <h1>{{ $user->name }} <span class="muted">&#64;{{ $user->handle }}</span></h1>
        <p class="stats">
            <span>Best score <strong>{{ number_format($user->best_score) }}</strong></span>
            @if ($user->favourite_mode)<span>Plays <strong>{{ \App\Models\User::MODES[$user->favourite_mode] }}</strong></span>@endif
            @if ($user->platform)<span>On <strong>{{ \App\Models\User::PLATFORMS[$user->platform] }}</strong></span>@endif
            <span>Joined <strong>{{ $user->created_at->format('M Y') }}</strong></span>
        </p>
        @if ($user->bio)<p class="bio">{{ $user->bio }}</p>@endif
        @if (auth()->id() === $user->id)<a href="{{ route('profile.edit') }}" class="btn btn-small btn-ghost">Edit profile</a>@endif
    </div>
</section>

<section>
    <div class="section-head"><h2>Shared worlds</h2>@if (auth()->id() === $user->id)<a href="{{ route('games.create') }}">Share another</a>@endif</div>
    @if ($games->isEmpty())
        <p class="muted">{{ $user->name }} has not shared a world yet.</p>
    @else
        <div class="grid cards">@each('games._card', $games, 'game')</div>
    @endif
</section>
@endsection
