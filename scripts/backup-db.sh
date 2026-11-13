#!/usr/bin/env bash
set -euo pipefail

project_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
backup_dir="${BACKUP_DIR:-$project_root/backups}"
mkdir -p "$backup_dir"
chmod 700 "$backup_dir"
umask 077

timestamp="$(date +%Y%m%d-%H%M%S)"
backup_file="$backup_dir/ift-result-tracking-$timestamp.sql"
temporary_file="$backup_file.tmp"
trap 'rm -f "$temporary_file"' EXIT

docker compose -f "$project_root/docker-compose.yml" exec -T db sh -ec \
  'MYSQL_PWD="$MARIADB_ROOT_PASSWORD" exec mariadb-dump --user=root --single-transaction --routines --events --triggers "$MARIADB_DATABASE"' \
  > "$temporary_file"
mv "$temporary_file" "$backup_file"
trap - EXIT
chmod 600 "$backup_file"
printf 'Database backup written to %s\n' "$backup_file"
