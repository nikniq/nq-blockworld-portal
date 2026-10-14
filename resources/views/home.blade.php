@extends('layouts.app')
@section('title', 'Mine by day, survive the night')

@section('content')
<section class="hero">
    <div>
        <h1>Mine by day.<br>Survive the night.</h1>
        <p class="lead">Blockworld is a Minecraft-style survival game with a rifle. A world that wraps around with no edges, dragons on the peaks, dinosaurs in the swamps, kangaroos in the outback and villages at war. Every texture and sound is generated when the game starts, so the whole thing is a single small download.</p>
        <div class="cta">
            <a href="{{ route('downloads.index') }}" class="btn btn-big">Download {{ $latest ? 'v'.$latest->version : '' }}</a>
            <a href="{{ route('games.index') }}" class="btn btn-ghost btn-big">Browse shared worlds</a>
        </div>
        <p class="muted small">macOS, Linux and Windows. No installer, no account needed to play.</p>
    </div>
    <div class="hero-art" aria-hidden="true">
        <div class="cube c1"></div><div class="cube c2"></div><div class="cube c3"></div><div class="cube c4"></div><div class="cube c5"></div>
    </div>
</section>

<section class="features">
    <h2>What's in the world</h2>
    <div class="grid">
        <article><h3>A world with no edges</h3><p>Walk east and you arrive from the west. Hills, a mountain band, beaches, a sea, caves, ore veins and ruins, in three world sizes up to 576x576 blocks.</p></article>
        <article><h3>Four game modes</h3><p>Survival is the normal game. Creative lets you fly and build. Zombie never sees the sun. Battle drops you into a war of the flags with iron gear and a rifle.</p></article>
        <article><h3>Day and night</h3><p>A six-minute cycle. Mine, craft and build by day; at nightfall zombies, spiders, creepers and skeleton archers rise wherever there is no torchlight. Giants walk every fifth night.</p></article>
        <article><h3>Dragons and dinosaurs</h3><p>A fire dragon circles the highest peak and a frost dragon the taiga. A tyrannosaur roams every world, brontosaur herds browse the swamps and raptor packs hunt the outback.</p></article>
        <article><h3>Australian wildlife</h3><p>Kangaroos kick when provoked, emus sprint off, koalas doze in the trees, platypuses paddle the rivers and crocodiles charge from warm shores. A kookaburra laughs at dawn.</p></article>
        <article><h3>Villages at war</h3><p>Redfort and Bluehaven send warbands to capture flags. Talk to villagers, take their quests, or plant your own flag and found a village of your own.</p></article>
        <article><h3>Craft, farm, fish, ride</h3><p>Swords and pickaxes in three tiers, bows and arrows, TNT, boats, beds, chests and doors. Grow wheat, catch fish, tame wolves and cats, ride horses.</p></article>
        <article><h3>Three art styles</h3><p>Switch live between crisp Pixel, brushed Painterly and the default Sketched look with wobbled blocks, ink outlines and cel-shaded light.</p></article>
        <article><h3>Online play</h3><p>Host a server and bring friends into the same world, with chat, a player list and arrows and rifles that work across the network.</p></article>
    </div>
</section>

<section class="goal">
    <h2>The goal</h2>
    <p>An ancient beacon tower stands far from spawn. The compass shows its bearing and the map marks it. Climb it and light the beacon with three diamond ore to conquer the world: five thousand points, fireworks and an achievement. The world carries on afterwards.</p>
</section>

@if ($games->isNotEmpty())
<section>
    <div class="section-head"><h2>Recently shared worlds</h2><a href="{{ route('games.index') }}">See all</a></div>
    <div class="grid cards">
        @each('games._card', $games, 'game')
    </div>
</section>
@endif

<section class="controls">
    <h2>Controls</h2>
    <table>
        <tr><th>Mouse / arrows</th><td>Look</td><th>W A S D</th><td>Move</td></tr>
        <tr><th>Left click</th><td>Shoot, swing or mine</td><th>Right click</th><td>Place block, use</td></tr>
        <tr><th>E</th><td>Crafting</td><th>R</th><td>Reload</td></tr>
        <tr><th>Space</th><td>Jump / swim up</td><th>Ctrl / Shift</th><td>Sprint / sneak</td></tr>
        <tr><th>M</th><td>Full map</td><th>K</th><td>Achievements</td></tr>
        <tr><th>F5</th><td>Third person</td><th>Esc</th><td>Pause and settings</td></tr>
    </table>
</section>
@endsection
