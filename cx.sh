#!/bin/bash
#================================================================
# LOCAL command-line assistant for the Blockworld portal — cx.sh
#
#   ./cx.sh            # interactive menu
#   ./cx.sh 20         # run an option directly
#   ./cx.sh 33 "migrate:status"   # …with a second parameter
#
# ⚠ Runs on your LOCAL machine only — never copy this to the server.
#   Its counterpart on the server is deploy.sh (pull + migrate + caches).
#
# Requires a vars.sh next to it (see vars.sh.example; gitignored).
#================================================================
clear

if [ ! -f ./vars.sh ]; then
  echo "vars.sh not found next to cx.sh — copy vars.sh.example to vars.sh and fill it in."
  exit 1
fi
source ./vars.sh

REMOTE_APP="${REMOTE_APP:-www/$PROJECT}"
APP_URL="${APP_URL:-https://$PROJECT}"
GAME_DIST="${GAME_DIST:-$HOME/repo/nq-blockworld/dist}"
APP_SSH="${APP_SSH:-ssh}"
APP_SCP="${APP_SCP:-scp}"

if [ "$(hostname -s 2>/dev/null)" = "${REMOTE_HOST%%.*}" ]; then
  echo "⚠ This looks like $REMOTE_HOST — cx.sh is the LOCAL script. Use deploy.sh here."
  exit 1
fi

#---------------------------------------------------------------
# Helpers
#---------------------------------------------------------------
log_history() {
  mkdir -p ./log
  printf '%s  %s\n' "$(date '+%Y-%m-%d %H:%M')" "$1" >> ./log/history.log
}

# Run a command on the server, in the app directory.
remote() {
  $APP_SSH "$REMOTE" "cd ~/$REMOTE_APP && $*"
}

# Same, with a terminal attached, for anything interactive or long-running.
remote_tty() {
  $APP_SSH -t "$REMOTE" "cd ~/$REMOTE_APP && $*"
}

need_param() {
  if [ -z "$PARAM2" ]; then
    read -r -p "$1: " PARAM2
  fi
}

banner() {
  echo ===========================================================
  echo "$1"
  echo ===========================================================
}

confirm() {
  echo "⚠ $1"
  read -r -p "Type 'yes' to continue: " reply
  [ "$reply" = "yes" ] || { echo "Skipped $SELECTION."; exit 0; }
}

# The server deploys by pulling, so anything not pushed is not deployed.
warn_unpushed() {
  if [ -n "$(git status --porcelain 2>/dev/null)" ]; then
    echo "⚠ Uncommitted local changes — these will NOT deploy:"
    git status --short
    echo
  fi
  UNPUSHED=$(git log --oneline @{u}.. 2>/dev/null)
  if [ -n "$UNPUSHED" ]; then
    echo "⚠ Commits not pushed — these will NOT deploy:"
    echo "$UNPUSHED"
    echo
    read -r -p "Deploy anyway? Type 'yes' to continue: " reply
    [ "$reply" = "yes" ] || { echo "Skipped $SELECTION"; exit 0; }
  fi
  echo "Local HEAD : $(git log --oneline -1)"
  echo
}

echo =============================================================
echo "Hi $USER — acting on $PROJECT ($REMOTE)"
echo What do you want to do?
echo -------------------------------------------------------------
echo "ROUTINE"
echo "20 : DEPLOY: pull + migrate + caches + verify on $PROJECT"
echo "21 : DEPLOY + import uploaded game builds"
echo "22 : WHAT'S LIVE: compare the server's HEAD with local"
echo "23 : MIGRATIONS: status (what is pending on the server)"
echo "24 : CACHES: refresh config/routes/views"
echo "25 : CACHES: clear everything (when a cached value looks stale)"
echo "26 : COMPOSER: install production dependencies"
echo "27 : APP KEY: generate the encryption key on the server (fixes MissingAppKeyException)"
echo ----------------------------------------------
echo DIAGNOSE
echo "30 : HEALTH: $APP_URL and the download page"
echo "31 : LOGS: tail the Laravel log"
echo "32 : LOGS: last errors"
echo "33 : ARTISAN: run a command (e.g. 'migrate:status')"
echo "34 : CONFIG: key settings on the server (secrets hidden)"
echo "35 : DISK: quota and what uploads are using"
echo ----------------------------------------------
echo "GAME BUILDS & CONTENT"
echo "40 : PUBLISH BUILD: upload $GAME_DIST/* to the server and import them"
echo "41 : RELEASES: list what is published, with download counts"
echo "42 : STATS: players and shared worlds"
echo ----------------------------------------------
echo MAINTENANCE
echo "70 : VERIFY: run deploy.sh's checks on their own"
echo "72 : MAINTENANCE MODE: on  [site goes down]"
echo "73 : MAINTENANCE MODE: off"
echo "74 : LOGS: truncate the Laravel log  [discards log history]"
echo "91 : BACKUP: pull the database and uploads to ./backup/"
echo ----------------------------------------------
echo ACCESS
echo "00 : CONNECT: to $REMOTE"
echo "01 : FIRST DEPLOY: clone the repo on the server and set it up"
echo "qq : Exit [Quit]"
echo =============================================================
if [ -n "$1" ]; then
  SELECTION=$1
else
  read -n 2 SELECTION
fi
PARAM2="${2:-}"
echo
echo "Your selection is : $SELECTION."

case "$SELECTION" in

  #-------------------------------------------------------------
  # ROUTINE
  #-------------------------------------------------------------
  "20" )
  banner "DEPLOY: $PROJECT"
  warn_unpushed
  remote_tty "./deploy.sh"
  log_history "Deployed $PROJECT"
  ;;

  "21" )
  banner "DEPLOY + IMPORT BUILDS: $PROJECT"
  warn_unpushed
  remote_tty "./deploy.sh --releases"
  log_history "Deployed $PROJECT with release import"
  ;;

  "22" )
  banner "WHAT'S LIVE on $PROJECT"
  echo "--- server ---"
  remote "git log --oneline -3 && git status --short"
  echo
  echo "--- local ---"
  git log --oneline -3
  echo
  LOCAL_HEAD=$(git rev-parse HEAD 2>/dev/null)
  SERVER_HEAD=$(remote "git rev-parse HEAD" 2>/dev/null | tr -d '\r')
  if [ "$LOCAL_HEAD" = "$SERVER_HEAD" ]; then
    echo "✓ server matches local HEAD"
  else
    echo "✗ server is NOT on your local HEAD"
    echo "  local : $LOCAL_HEAD"
    echo "  server: $SERVER_HEAD"
  fi
  ;;

  "23" )
  banner "MIGRATIONS: status on $PROJECT"
  remote "php artisan migrate:status"
  ;;

  "24" )
  banner "CACHES: refresh on $PROJECT"
  remote "php artisan optimize"
  log_history "Refreshed caches on $PROJECT"
  ;;

  "25" )
  banner "CACHES: clear everything on $PROJECT"
  remote "php artisan optimize:clear"
  echo "Run 24 to rebuild them."
  log_history "Cleared all caches on $PROJECT"
  ;;

  "26" )
  banner "COMPOSER: install production dependencies"
  remote_tty "composer install --no-dev --optimize-autoloader --no-interaction"
  log_history "Installed composer dependencies on $PROJECT"
  ;;

  "27" )
  banner "APP KEY on $PROJECT"
  # MissingAppKeyException on every request means .env has no APP_KEY.
  # Rotating an existing key invalidates sessions, remember-me cookies and
  # any encrypted values, so an existing key is kept unless you insist.
  if remote "grep -qE '^APP_KEY=.+' .env" 2>/dev/null; then
    echo "The server already has an APP_KEY."
    confirm "Regenerating it logs every player out and breaks anything encrypted with the old key."
  fi
  remote_tty "test -f .env || cp .env.example .env; php artisan key:generate --force && php artisan optimize"
  if remote "grep -qE '^APP_DEBUG=true' .env" 2>/dev/null; then
    echo "⚠ APP_DEBUG=true on the server: errors show stack traces to the public."
    read -r -p "Turn it off now? Type 'yes' to continue: " reply
    if [ "$reply" = "yes" ]; then
      remote "sed -i 's/^APP_DEBUG=.*/APP_DEBUG=false/; s/^APP_ENV=.*/APP_ENV=production/' .env && php artisan optimize"
      echo "APP_DEBUG is now false."
    fi
  fi
  log_history "Generated APP_KEY on $PROJECT"
  ;;

  #-------------------------------------------------------------
  # DIAGNOSE
  #-------------------------------------------------------------
  "30" )
  banner "HEALTH: $APP_URL"
  for path in / /download /games /players; do
    printf '%-10s ' "$path"
    curl -sS -o /dev/null -w 'status %{http_code}  total %{time_total}s\n' --max-time 25 "$APP_URL$path" \
      || echo "✗ no response"
  done
  echo
  echo "--- /up (framework health) ---"
  curl -sS -o /dev/null -w 'status %{http_code}\n' --max-time 25 "$APP_URL/up" || echo "✗ no response"
  ;;

  "31" )
  banner "LOGS: tail on $PROJECT (Ctrl-C to stop)"
  remote_tty "tail -n 40 -f storage/logs/laravel.log"
  ;;

  "32" )
  banner "LOGS: recent errors on $PROJECT"
  remote "grep -nE '\.(ERROR|CRITICAL|ALERT|EMERGENCY):' storage/logs/laravel.log | tail -n 25" \
    || echo "No matching lines (or no log file yet)."
  ;;

  "33" )
  banner "ARTISAN on $PROJECT"
  need_param "artisan command (without the word 'artisan')"
  case "$PARAM2" in
    tinker*) echo "⚠ tinker needs an interactive PHP CLI; SSH php on shared hosting is cgi-fcgi and will die." ;;
  esac
  remote_tty "php artisan $PARAM2"
  log_history "Ran artisan $PARAM2 on $PROJECT"
  ;;

  "34" )
  banner "CONFIG on $PROJECT"
  echo "--- environment ---"
  remote "grep -E '^(APP_ENV|APP_DEBUG|APP_URL|SESSION_DRIVER|CACHE_STORE|QUEUE_CONNECTION|MAIL_MAILER|DB_CONNECTION|FILESYSTEM_DISK)=' .env | sort"
  echo
  echo "--- keys present (values hidden) ---"
  remote "grep -oE '^[A-Z_]*(KEY|SECRET|TOKEN|PASSWORD)[A-Z_]*=' .env | tr -d '=' | sort | sed 's/^/  /'"
  ;;

  "35" )
  banner "DISK: quota and uploads on $PROJECT"
  remote "du -sh . 2>/dev/null; echo; echo 'uploads:'; du -sh storage/app/private/releases storage/app/private/games storage/app/public/avatars storage/app/public/screenshots 2>/dev/null; echo; echo 'largest directories:'; du -sh */ 2>/dev/null | sort -rh | head -10"
  ;;

  #-------------------------------------------------------------
  # GAME BUILDS & CONTENT
  #-------------------------------------------------------------
  "40" )
  banner "PUBLISH BUILD: $GAME_DIST → $PROJECT"
  # Builds come from ./cx.sh in the game repo, one platform per machine, so
  # this uploads whatever archives are present and lets ReleaseSeeder upsert
  # them. Archives already on the server are skipped by name.
  shopt -s nullglob
  ARCHIVES=("$GAME_DIST"/*.zip "$GAME_DIST"/*.tar.gz)
  shopt -u nullglob
  if [ ${#ARCHIVES[@]} -eq 0 ]; then
    echo "No archives in $GAME_DIST. Run ./cx.sh in the game repo first."
    exit 1
  fi
  printf '  %s\n' "${ARCHIVES[@]##*/}"
  [ -f "$GAME_DIST/SHA256SUMS" ] && ARCHIVES+=("$GAME_DIST/SHA256SUMS")
  remote "mkdir -p storage/app/private/releases"
  $APP_SCP "${ARCHIVES[@]}" "$REMOTE:~/$REMOTE_APP/storage/app/private/releases/"
  remote_tty "BLOCKWORLD_DIST=\$PWD/storage/app/private/releases php artisan db:seed --class=ReleaseSeeder --force"
  log_history "Published builds: ${ARCHIVES[*]##*/}"
  ;;

  "41" )
  banner "RELEASES on $PROJECT"
  remote "php artisan portal:releases"
  ;;

  "42" )
  banner "STATS on $PROJECT"
  remote "php artisan portal:stats"
  ;;

  #-------------------------------------------------------------
  # MAINTENANCE
  #-------------------------------------------------------------
  "70" )
  banner "VERIFY on $PROJECT"
  remote "php artisan migrate:status | grep -c Pending | sed 's/^/pending migrations: /'; test -L public_html/storage && echo 'storage link: ok' || echo 'storage link: MISSING'; test -w storage/logs && echo 'storage/logs: writable' || echo 'storage/logs: NOT writable'"
  ;;

  "72" )
  banner "MAINTENANCE MODE: ON"
  confirm "$APP_URL will return 503 to everyone until you run 73."
  remote_tty "php artisan down --retry=60"
  log_history "Maintenance mode ON for $PROJECT"
  ;;

  "73" )
  banner "MAINTENANCE MODE: OFF"
  remote_tty "php artisan up"
  log_history "Maintenance mode OFF for $PROJECT"
  ;;

  "74" )
  banner "LOGS: truncate the Laravel log"
  confirm "This discards the Laravel log on $PROJECT. Run 32 first if you have not read the recent errors."
  remote "test -f storage/logs/laravel.log && : > storage/logs/laravel.log && echo 'laravel.log truncated.' || echo 'No laravel.log to truncate.'"
  log_history "Truncated the Laravel log on $PROJECT"
  ;;

  "91" )
  banner "BACKUP: database and uploads from $PROJECT"
  # Uploaded saves, avatars and screenshots live only on the server, so they
  # are the part of this site that cannot be rebuilt from git.
  STAMP=$(date '+%Y%m%d-%H%M')
  mkdir -p "./backup/$STAMP"
  remote "tar czf - storage/app/private/games storage/app/public database/database.sqlite 2>/dev/null" > "./backup/$STAMP/uploads-and-db.tgz"
  ls -lh "./backup/$STAMP"
  echo "If the site uses MySQL rather than SQLite, dump it separately with mysqldump on the server."
  log_history "Backed up $PROJECT to backup/$STAMP"
  ;;

  #-------------------------------------------------------------
  # ACCESS
  #-------------------------------------------------------------
  "00" )
  banner "CONNECT: to $REMOTE"
  $APP_SSH "$REMOTE"
  log_history "Connect to remote"
  ;;

  "01" )
  banner "FIRST DEPLOY: set up $PROJECT on the server"
  # One-time: clone into ~/$REMOTE_APP, create .env, generate the key and run
  # deploy.sh. The web root must then be pointed at ~/$REMOTE_APP/public_html
  # in the hosting control panel.
  REPO_URL=$(git remote get-url origin)
  confirm "This clones $REPO_URL into ~/$REMOTE_APP on $REMOTE and creates a fresh .env there."
  $APP_SSH -t "$REMOTE" "test -d ~/$REMOTE_APP/.git && echo 'already cloned' || git clone $REPO_URL ~/$REMOTE_APP; cd ~/$REMOTE_APP && test -f .env || { cp .env.example .env && sed -i 's/^APP_ENV=.*/APP_ENV=production/; s/^APP_DEBUG=.*/APP_DEBUG=false/; s#^APP_URL=.*#APP_URL=$APP_URL#' .env; php artisan key:generate --force; }; touch database/database.sqlite; chmod +x deploy.sh; ./deploy.sh"
  echo
  echo "Now point the site's document root at ~/$REMOTE_APP/public_html and run 30."
  log_history "First deploy of $PROJECT"
  ;;

  "qq" )
  echo Quit
  exit 0
  ;;

  * )
  echo
  echo "Not a recognized option."
  ;;

esac
