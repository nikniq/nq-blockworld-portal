#!/usr/bin/env bash
# Production deploy for the Blockworld portal. Run ON the server, from the app root:
#
#   ./deploy.sh              # pull + composer (if the lockfile moved) + migrate + caches + verify
#   ./deploy.sh --releases   # …and import any new game builds from storage/app/private/releases
#                            # (cx.sh option 40 uploads them there first)
#
# Shared hosting has no Node, and this app needs none: views are Blade and the
# stylesheet is a plain file under public_html/css, so nothing is built here.
set -euo pipefail
cd "$(dirname "$0")"

echo "→ git pull"
LOCK_BEFORE=$(git rev-parse HEAD:composer.lock 2>/dev/null || echo none)
git pull --ff-only
LOCK_AFTER=$(git rev-parse HEAD:composer.lock 2>/dev/null || echo none)

if [ "$LOCK_BEFORE" != "$LOCK_AFTER" ] || [ ! -d vendor ]; then
    if command -v composer >/dev/null 2>&1; then
        echo "→ composer install (lockfile changed or vendor missing)"
        composer install --no-dev --optimize-autoloader --no-interaction
    else
        echo "⚠ composer.lock changed but composer is not on PATH. Install dependencies by hand before trusting this deploy."
    fi
fi

# A fresh checkout has no .env, and a hand-made one often has no APP_KEY, which
# 500s every request with MissingAppKeyException. Create and key it here so
# a first deploy cannot go live half-configured.
if [ ! -f .env ]; then
    echo "→ creating .env from .env.example (production, debug off)"
    cp .env.example .env
    sed -i.bak 's/^APP_ENV=.*/APP_ENV=production/; s/^APP_DEBUG=.*/APP_DEBUG=false/' .env && rm -f .env.bak
fi
if ! grep -qE '^APP_KEY=.+' .env; then
    echo "→ key:generate (APP_KEY was empty)"
    php artisan key:generate --force
fi
touch database/database.sqlite

echo "→ migrate"
php artisan migrate --force

if [ "${1:-}" = "--releases" ]; then
    # ReleaseSeeder upserts on version+platform+arch, so re-running is safe.
    # BLOCKWORLD_DIST points it at the already-uploaded archives instead of a
    # sibling game checkout that does not exist on the server.
    echo "→ import game builds"
    BLOCKWORLD_DIST="$PWD/storage/app/private/releases" php artisan db:seed --class=ReleaseSeeder --force
fi

# Avatars and screenshots are served through this link; a fresh clone has none.
if [ ! -e public_html/storage ]; then
    echo "→ storage:link"
    php artisan storage:link
fi

echo "→ cache config/routes/views"
php artisan optimize

echo "→ verify"
php artisan migrate:status | grep -q Pending && { echo "✗ migrations still pending"; exit 1; }
[ -L public_html/storage ] || { echo "✗ public_html/storage link missing"; exit 1; }
[ -w storage/logs ] || { echo "✗ storage/logs is not writable"; exit 1; }
grep -qE '^APP_DEBUG=true' .env && echo "⚠ APP_DEBUG=true: stack traces are public. Set APP_DEBUG=false in .env and rerun."

echo "✓ deployed $(git log --oneline -1)"
