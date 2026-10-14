@extends('layouts.app')
@section('title', 'Join')

@section('content')
<div class="narrow">
    <h1>Create your player profile</h1>
    <p class="muted">An account lets you share worlds and keep a public profile. You never need one to play.</p>
    <form method="post" action="{{ route('register') }}">
        @csrf
        <label>Name <input name="name" value="{{ old('name') }}" required maxlength="60"></label>
        <label>Handle <input name="handle" value="{{ old('handle') }}" required minlength="3" maxlength="32" pattern="[A-Za-z0-9_]+"><small>Letters, numbers and underscores. Your profile lives at /u/handle.</small></label>
        <label>Email <input type="email" name="email" value="{{ old('email') }}" required></label>
        <label>Password <input type="password" name="password" required minlength="8"></label>
        <label>Confirm password <input type="password" name="password_confirmation" required></label>
        <label>I play on
            <select name="platform">
                <option value="">Not saying</option>
                @foreach (\App\Models\User::PLATFORMS as $k => $v)<option value="{{ $k }}" @selected(old('platform') === $k)>{{ $v }}</option>@endforeach
            </select>
        </label>
        <button class="btn">Join</button>
        <p class="small muted">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
    </form>
</div>
@endsection
