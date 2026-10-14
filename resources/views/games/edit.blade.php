@extends('layouts.app')
@section('title', 'Edit '.$game->title)

@section('content')
<div class="narrow">
    <h1>Edit world</h1>
    <form method="post" action="{{ route('games.update', $game) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('games._form')
        <button class="btn">Save changes</button>
        <a href="{{ route('games.show', $game) }}" class="btn btn-ghost">Cancel</a>
    </form>
</div>
@endsection
