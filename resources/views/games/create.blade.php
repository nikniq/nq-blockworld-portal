@extends('layouts.app')
@section('title', 'Share a world')

@section('content')
<div class="narrow">
    <h1>Share a world</h1>
    <p class="muted">Press S on the pause screen to save, then upload world.sav from your Blockworld data folder.</p>
    <form method="post" action="{{ route('games.store') }}" enctype="multipart/form-data">
        @csrf
        @include('games._form')
        <button class="btn">Share</button>
    </form>
</div>
@endsection
