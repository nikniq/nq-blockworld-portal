@extends('layouts.app')
@section('title', 'Edit profile')

@section('content')
<div class="narrow">
    <h1>Your profile</h1>
    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <label>Name <input name="name" value="{{ old('name', $user->name) }}" required maxlength="60"></label>
        <label>Handle <input value="{{ $user->handle }}" disabled><small>Handles cannot be changed.</small></label>
        <label>About you <textarea name="bio" maxlength="500" rows="4">{{ old('bio', $user->bio) }}</textarea></label>
        <label>I play on
            <select name="platform">
                <option value="">Not saying</option>
                @foreach (\App\Models\User::PLATFORMS as $k => $v)<option value="{{ $k }}" @selected(old('platform', $user->platform) === $k)>{{ $v }}</option>@endforeach
            </select>
        </label>
        <label>Favourite mode
            <select name="favourite_mode">
                <option value="">Not saying</option>
                @foreach (\App\Models\User::MODES as $k => $v)<option value="{{ $k }}" @selected(old('favourite_mode', $user->favourite_mode) === $k)>{{ $v }}</option>@endforeach
            </select>
        </label>
        <label>Best score <input type="number" name="best_score" min="0" value="{{ old('best_score', $user->best_score) }}"><small>From the main menu of the game.</small></label>
        <label>Avatar <input type="file" name="avatar" accept="image/*"><small>PNG or JPEG up to 2 MB.</small></label>
        <button class="btn">Save</button>
    </form>
</div>
@endsection
