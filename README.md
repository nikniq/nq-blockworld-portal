# Blockworld Portal

The website for [Blockworld](../nq-blockworld): introduces the game, serves the packaged builds,
holds player profiles and lets players share their saved worlds. Laravel 13, SQLite by default,
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
