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
| `/auth/google` | "Continue with Google" (shown once `GOOGLE_CLIENT_ID` is set): signs in a linked account, links an existing account with the same email, or creates one with a handle made from the Google name |

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

## Google sign-in

Create an OAuth client (type *Web application*) in the Google Cloud console with the authorised
redirect URI `APP_URL/auth/google/callback`, then set `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET`
in `.env` (locally) or with `./cx.sh 38` (on the server). The button is hidden until the id is set.
Google-only accounts have no password; the profile page says they signed in with Google.

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
| `deploy.sh` | the **server** | `git pull`, composer install when the lockfile moved, migrate (SQLite snapshot first, maintenance mode while the schema changes), storage link, caches, verify. Also `--check` (dry run), `--to <ref>` (rollback), `--set KEY=VALUE`, `--verify`, `--backup`, `--prune`, `--releases` (import uploaded game builds). |
| `cx.sh` | your **local machine** | Menu: deploy (20/21), what's live (22), preflight (28), rollback (29), app key (27), health (30), logs (31/32), env values (36), Google sign-in (38), publish a game build (40), releases and stats (41/42), players and admins (43–45), prune and snapshots (75/76), backup and restore (91/92), local check and serve (10/11), first-time server setup (01). |

`cx.sh` reads `vars.sh` (gitignored). Copy `vars.sh.example` to `vars.sh` and fill in the host, account
and app path. Never copy `cx.sh` or `vars.sh` to the server.

Publishing a new game build: run `./cx.sh` in the game repo on each platform, then `./cx.sh 40` here.
It uploads the archives and SHA256SUMS into `storage/app/private/releases` on the server and runs
`ReleaseSeeder` against them.

Artisan commands for the server: `portal:releases`, `portal:stats`, `portal:users` (`--admin`, `--revoke`,
`--delete`), `portal:prune`.
