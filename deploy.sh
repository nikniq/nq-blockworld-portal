#!/usr/bin/env bash
# Production deploy and upkeep for the Blockworld portal. Run ON the server, from the app root:
#
#   ./deploy.sh                  # pull + composer (if the lockfile moved) + migrate + caches + verify
#   ./deploy.sh --releases       # …and import any new game builds from storage/app/private/releases
#                                # (cx.sh option 40 uploads them there first)
#   ./deploy.sh --check          # dry run: what a deploy would pull and migrate; changes nothing
#   ./deploy.sh --to <ref>       # roll back (or forward) to a commit or tag, then migrate and cache
#   ./deploy.sh --verify         # just the post-deploy checks
#   ./deploy.sh --set KEY=VALUE  # set one .env value (e.g. GOOGLE_CLIENT_ID) and refresh the config cache
#   ./deploy.sh --backup         # snapshot the SQLite database into storage/backups/
#   ./deploy.sh --prune          # delete expired sessions, stale cache rows and backups older than 30 days
#
# Shared hosting has no Node, and this app needs none: views are Blade and the
# stylesheet is a plain file under public_html/css, so nothing is built here.
set -euo pipefail
cd "$(dirname "$0")"

DB_FILE=database/database.sqlite
BACKUPS=storage/backups
KEEP_DAYS=30

say() { echo "→ $*"; }

# Snapshot the SQLite database (if that is what the site uses) before anything
# touches the schema. Cheap, and the only way back from a bad migration.
backup_db() {
    if [ -s "$DB_FILE" ] && grep -qE '^DB_CONNECTION=sqlite' .env 2>/dev/null; then
        mkdir -p "$BACKUPS"
        local out="$BACKUPS/db-$(date '+%Y%m%d-%H%M%S').sqlite"
        if command -v sqlite3 >/dev/null 2>&1; then
            sqlite3 "$DB_FILE" ".backup '$out'"
        else
            cp "$DB_FILE" "$out"
        fi
        gzip -f "$out"
        say "database backed up to $out.gz"
    fi
}

set_env() {
    local kv="$1" key="${1%%=*}" val="${1#*=}"
    case "$kv" in *=*) ;; *) echo "usage: --set KEY=VALUE"; exit 2 ;; esac
    [[ "$key" =~ ^[A-Z][A-Z0-9_]*$ ]] || { echo "✗ $key is not an environment variable name"; exit 2; }
    [ -f .env ] || cp .env.example .env
    # Quote values with spaces or shell characters; Laravel's parser understands double quotes.
    [[ "$val" =~ [[:space:]\#\$] ]] && val="\"$val\""
    if grep -qE "^$key=" .env; then
        awk -v k="$key" -v v="$val" 'index($0, k"=")==1 {print k"="v; next} {print}' .env > .env.tmp && mv .env.tmp .env
    else
        printf '%s=%s\n' "$key" "$val" >> .env
    fi
    say "$key set"
    php artisan config:cache >/dev/null
}

verify() {
    say "verify"
    php artisan migrate:status | grep -q Pending && { echo "✗ migrations still pending"; exit 1; }
    [ -L public_html/storage ] || { echo "✗ public_html/storage link missing"; exit 1; }
    [ -w storage/logs ] || { echo "✗ storage/logs is not writable"; exit 1; }
    grep -qE '^APP_KEY=.+' .env || { echo "✗ APP_KEY is empty"; exit 1; }
    grep -qE '^APP_DEBUG=true' .env && echo "⚠ APP_DEBUG=true: stack traces are public. Set APP_DEBUG=false in .env and rerun."
    grep -qE '^APP_URL=https://' .env || echo "⚠ APP_URL is not https: OAuth callbacks and secure cookies need it."
    if grep -qE '^GOOGLE_CLIENT_ID=.+' .env; then
        grep -qE '^GOOGLE_CLIENT_SECRET=.+' .env || echo "⚠ GOOGLE_CLIENT_ID is set but GOOGLE_CLIENT_SECRET is empty"
        echo "Google sign-in: on (authorised redirect URI must be $(grep -E '^APP_URL=' .env | cut -d= -f2-)/auth/google/callback)"
    else
        echo "Google sign-in: off (set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET with --set to enable it)"
    fi
    echo "✓ ok at $(git log --oneline -1)"
}

MODE="${1:-}"
case "$MODE" in
    --set)      set_env "${2:-}"; exit 0 ;;
    --verify)   verify; exit 0 ;;
    --backup)   backup_db; ls -lh "$BACKUPS" 2>/dev/null | tail -n +2; exit 0 ;;
    --prune)
        say "prune"
        php artisan portal:prune --days="$KEEP_DAYS"
        exit 0 ;;
    --check)
        say "check (nothing will change)"
        git fetch --quiet origin
        BRANCH=$(git rev-parse --abbrev-ref HEAD)
        echo "server : $(git log --oneline -1)"
        echo "origin : $(git log --oneline -1 "origin/$BRANCH")"
        INCOMING=$(git log --oneline "HEAD..origin/$BRANCH")
        if [ -n "$INCOMING" ]; then
            echo "would pull:"; echo "$INCOMING" | sed 's/^/  /'
            git diff --quiet HEAD "origin/$BRANCH" -- composer.lock || echo "composer.lock changes: composer install would run"
            NEWMIG=$(git diff --name-only --diff-filter=A HEAD "origin/$BRANCH" -- database/migrations | sed 's/^/  /')
            [ -n "$NEWMIG" ] && { echo "new migrations:"; echo "$NEWMIG"; }
        else
            echo "nothing to pull"
        fi
        PENDING=$(php artisan migrate:status | grep -c Pending || true)
        echo "pending migrations on the server now: $PENDING"
        [ -n "$(git status --porcelain)" ] && { echo "⚠ server checkout has local changes:"; git status --short; }
        exit 0 ;;
    --to)
        REF="${2:?usage: --to <commit|tag>}"
        say "fetch"
        git fetch --quiet --tags origin
        git rev-parse --verify --quiet "$REF^{commit}" >/dev/null || { echo "✗ unknown ref $REF"; exit 1; }
        echo "server is at $(git log --oneline -1); moving to $(git log --oneline -1 "$REF")"
        LOCK_BEFORE=$(git rev-parse HEAD:composer.lock 2>/dev/null || echo none)
        backup_db
        if git show-ref --verify --quiet "refs/heads/$REF"; then
            git checkout --quiet "$REF" && git pull --ff-only --quiet
        else
            git checkout --quiet --detach "$REF"
            echo "⚠ detached at $REF; plain deploys will fail until you run: ./deploy.sh --to main"
        fi
        ;;
    ""|--releases)
        say "git pull"
        LOCK_BEFORE=$(git rev-parse HEAD:composer.lock 2>/dev/null || echo none)
        git pull --ff-only
        ;;
    *)
        sed -n '2,13p' "$0"; exit 2 ;;
esac

LOCK_AFTER=$(git rev-parse HEAD:composer.lock 2>/dev/null || echo none)
if [ "$LOCK_BEFORE" != "$LOCK_AFTER" ] || [ ! -d vendor ]; then
    if command -v composer >/dev/null 2>&1; then
        say "composer install (lockfile changed or vendor missing)"
        composer install --no-dev --optimize-autoloader --no-interaction
    else
        echo "⚠ composer.lock changed but composer is not on PATH. Install dependencies by hand before trusting this deploy."
    fi
fi

# A fresh checkout has no .env, and a hand-made one often has no APP_KEY, which
# 500s every request with MissingAppKeyException. Create and key it here so
# a first deploy cannot go live half-configured.
if [ ! -f .env ]; then
    say "creating .env from .env.example (production, debug off)"
    cp .env.example .env
    sed -i.bak 's/^APP_ENV=.*/APP_ENV=production/; s/^APP_DEBUG=.*/APP_DEBUG=false/' .env && rm -f .env.bak
fi
if ! grep -qE '^APP_KEY=.+' .env; then
    say "key:generate (APP_KEY was empty)"
    php artisan key:generate --force
fi
touch "$DB_FILE"

# Only take the site down when the schema is about to change.
if php artisan migrate:status | grep -q Pending; then
    [ "$MODE" = --to ] || backup_db
    say "migrate (site in maintenance mode meanwhile)"
    php artisan down --retry=30 --refresh=15 >/dev/null || true
    php artisan migrate --force || { php artisan up >/dev/null; echo "✗ migration failed; site is back up on the old schema (restore from $BACKUPS if needed)"; exit 1; }
    php artisan up >/dev/null
else
    say "migrate: nothing pending"
fi

if [ "$MODE" = "--releases" ]; then
    # ReleaseSeeder upserts on version+platform+arch, so re-running is safe.
    # BLOCKWORLD_DIST points it at the already-uploaded archives instead of a
    # sibling game checkout that does not exist on the server.
    say "import game builds"
    BLOCKWORLD_DIST="$PWD/storage/app/private/releases" php artisan db:seed --class=ReleaseSeeder --force
fi

# Avatars and screenshots are served through this link; a fresh clone has none.
if [ ! -e public_html/storage ]; then
    say "storage:link"
    php artisan storage:link
fi

say "cache config/routes/views"
php artisan optimize

verify
echo "✓ deployed $(git log --oneline -1)"
