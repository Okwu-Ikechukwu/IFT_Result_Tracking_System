#!/usr/bin/env bash
set -euo pipefail

if [[ $# -ne 1 || ! -f "$1" ]]; then
  printf 'Usage: %s path/to/backup.sql\n' "$0" >&2
  exit 2
fi

backup_file="$(cd "$(dirname "$1")" && pwd)/$(basename "$1")"
printf 'This restore can replace tables and data in the configured application database.\n'
read -r -p "Type RESTORE to continue with $backup_file: " confirmation
if [[ "$confirmation" != "RESTORE" ]]; then
  printf 'Restore cancelled.\n'
  exit 1
fi

project_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
docker compose -f "$project_root/docker-compose.yml" exec -T db sh -ec \
  'MYSQL_PWD="$MARIADB_ROOT_PASSWORD" exec mariadb --user=root "$MARIADB_DATABASE"' \
  < "$backup_file"
printf 'Database restore completed from %s\n' "$backup_file"
