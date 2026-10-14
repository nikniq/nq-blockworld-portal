# Blockworld Portal

The website for [Blockworld](../nq-blockworld): introduces the game, serves the packaged builds,
holds player profiles and lets players share their saved worlds. Laravel 13, SQLite by default, web root `public_html/`,
plain Blade and a single stylesheet (no Node build step).

## Pages

| Route | What it does |
|---|---|
| `/` | Landing page: feature tour, the goal, recent shared worlds, controls |
| `/download` | Builds per platform (macOS, Linux, Windows) with size, SHA-256 and install notes; files stream from `storage/app/private/releases` and count downloads |
| `/games` | Shared worlds, filterable by mode and sortable by newest, most downloaded or highest score |
| `/games/{slug}` | One world: screenshot, stats, description, download of `world.sav` |
| `/share` | Upload a `world.sav` (max 64 MB) with title, mode, size, score, nights and a screenshot (login required) |
| `/players`, `/u/{handle}` | Player directory and public profiles (bio, platform, favourite mode, best score, avatar, shared worlds) |
| `/register`, `/login`, `/profile` | Account creation, login and profile editing |

Owners (and users with `is_admin`) can edit or remove their worlds.

## Setup

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate
php artisan storage:link          # avatars and screenshots live on the public disk
php artisan db:seed               # imports builds from ../nq-blockworld/dist (see below)
php artisan serve
```

## Publishing a build

Run `./cx.sh` in the game repo on each platform; it writes `dist/Blockworld-<ver>-<platform>-<arch>.zip`
(or `.tar.gz`) plus `SHA256SUMS`. Then on the portal:

```bash
php artisan db:seed --class=ReleaseSeeder            # reads ../nq-blockworld/dist
BLOCKWORLD_DIST=/path/to/dist php artisan db:seed --class=ReleaseSeeder
```

The seeder copies each archive into `storage/app/private/releases`, records size and checksum, and
upserts on version + platform + arch, so re-running it is safe.

## Tests

```bash
php artisan test
vendor/bin/pint
```
# nq-blockworld-portal

## Deploying

Production is shared hosting with no Node, which this app does not need. Two scripts, running in
opposite places:

| Script | Runs on | Purpose |
|---|---|---|
| `deploy.sh` | the **server** | `git pull`, composer install when the lockfile moved, migrate, storage link, caches, verify. `--releases` also imports uploaded game builds. |
| `cx.sh` | your **local machine** | Menu: deploy (20/21), what's live (22), health (30), logs (31/32), publish a game build (40), releases and stats (41/42), backup (91), first-time server setup (01). |

`cx.sh` reads `vars.sh` (gitignored). Copy `vars.sh.example` to `vars.sh` and fill in the host, account
and app path. Never copy `cx.sh` or `vars.sh` to the server.

Publishing a new game build: run `./cx.sh` in the game repo on each platform, then `./cx.sh 40` here.
It uploads the archives and SHA256SUMS into `storage/app/private/releases` on the server and runs
`ReleaseSeeder` against them.
