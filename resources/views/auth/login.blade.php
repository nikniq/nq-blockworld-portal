@extends('layouts.app')
@section('title', 'Log in')

@section('content')
<div class="narrow">
    <h1>Log in</h1>
    <form method="post" action="{{ route('login') }}">
        @csrf
        <label>Email <input type="email" name="email" value="{{ old('email') }}" required autofocus></label>
        <label>Password <input type="password" name="password" required></label>
        <label class="check"><input type="checkbox" name="remember" value="1"> Stay logged in</label>
        <button class="btn">Log in</button>
        <p class="small muted">New here? <a href="{{ route('register') }}">Create a profile</a></p>
    </form>
</div>
@endsection
