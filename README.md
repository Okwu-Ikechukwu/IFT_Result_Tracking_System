# IFT Result Tracking System

PHP/MySQL application for recording and tracking physical examination results. The existing public search, staff pages, movement history, reports, CSV export, and admin/HOD role split are retained.

## Project layout

```text
app/                  Configuration and shared PHP components
bin/                  CLI-only account setup
database/             Fresh-install schema and manual reset SQL
docker/               Apache and PHP container configuration
public/               Apache document root, pages, API, CSS, JS, and assets
scripts/              Database backup and restore commands
archive/              Preserved source copies
Dockerfile
docker-compose.yml
composer.json
```

## Requirements

- PHP 8.2 or later with PDO MySQL for non-Docker development
- Composer 2 for PHPMailer
- MariaDB 10.6+ or a compatible MySQL server
- Docker Engine and Docker Compose v2 for the container setup

## Local development

Create an empty database, then load the fresh-install schema. It creates the 40 courses, 20 lecturers, two lecturer assignments per course, office/stage definitions, and settings. It does not create staff accounts or drop existing tables.

```sh
mysql -u root -p -e "CREATE DATABASE ift_result_tracking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u root -p ift_result_tracking < database/schema.sql
composer install
export DB_HOST=127.0.0.1 DB_PORT=3306 DB_NAME=ift_result_tracking DB_USER=root DB_PASS=''
export APP_ENV=development APP_URL=http://localhost:8080
php -S 127.0.0.1:8080 -t public
```

In another terminal, create the initial administrator. The script prompts for account details, hashes the password with `password_hash()`, and does not accept passwords as command-line arguments:

```sh
php bin/create-user.php --role=admin
```

Create an HOD account after the first administrator exists:

```sh
php bin/create-user.php --role=hod
```

The account setup tool is outside the web document root and is not available through an HTTP request. Use a terminal so the password prompt can hide typed characters.

## Docker setup

The app runs in the official PHP 8.4 Apache image, with Apache serving only `public/`. MariaDB 11.4.13 LTS runs as a separate service on a private Docker network. The official Noble-based MariaDB image declares an x86-64 baseline for this release line, so it does not require AVX on the target VM. The application port is published only on host loopback for the existing Nginx proxy.

1. Copy `.env.example` to `.env`.
2. Replace both database password placeholders with different, long random values. Set `APP_URL` to the local URL or final HTTPS URL, for example `http://localhost:8080` locally.
3. Start the services and wait for the database health check:

   ```sh
   docker compose up -d --build
   docker compose ps
   ```

4. Create the first administrator and, optionally, an HOD account:

   ```sh
   docker compose exec -it app php bin/create-user.php --role=admin
   docker compose exec -it app php bin/create-user.php --role=hod
   ```

5. Check application and database readiness:

   ```sh
   curl --fail http://127.0.0.1:8080/health.php
   ```

The first database container startup imports `database/schema.sql` only when the named database volume is empty. Later container starts and application rebuilds do not re-run the schema or remove database records. The database has no published host port.

Stop the containers while preserving database files:

```sh
docker compose down
```

Start again with `docker compose up -d`. Do not use `docker compose down -v` unless intentionally deleting the local database volume.

## Environment variables

| Variable | Purpose |
| --- | --- |
| `APP_ENV` | Use `production` in Docker; production requires DB host/name/user/password and `APP_URL`. |
| `APP_NAME` | Display name in pages and email. |
| `APP_URL` | Public base URL used in tracking links and email. |
| `APP_PORT` | Host loopback port for Apache; defaults to `8080`. |
| `DB_HOST` | Database hostname. Compose sets this to `db` for the app container. |
| `DB_PORT` | Database port; defaults to `3306`. |
| `DB_NAME` | Application database; also used for the MariaDB first-volume initializer. |
| `DB_USER` / `DB_PASS` | Application database credentials. |
| `DB_ROOT_PASSWORD` | MariaDB root password for database maintenance scripts. |
| `IFT_SMTP_HOST` | SMTP server hostname; blank disables mail delivery. |
| `IFT_SMTP_USER` / `IFT_SMTP_PASS` | SMTP authentication credentials. |
| `IFT_SMTP_PORT` | SMTP TLS port; defaults to `587`. |
| `IFT_FROM_EMAIL` | Sender address used for notifications. |

The app uses Africa/Lagos time. Development defaults are intended for a local database only. Production mode fails at startup if its required database and URL variables are missing. SMTP variables are optional; when absent, notification attempts are recorded as not configured. Never put real `.env` values in Git, screenshots, or support logs.

## Database initialization and recovery

`database/schema.sql` is for an empty database. In Docker, the MariaDB image runs it only during initialization of an empty `database_data` volume. It contains no `DROP TABLE` statements and does not create default accounts. Existing data is not automatically imported or overwritten.

`database/reset.sql` is a separate destructive, manual table reset. Never use it on a database that contains data you need. Back up first and verify the target database before any manual reset.

For an existing installation created from the old schema, back up first, manually run `database/migrations/001-disable-demo-accounts.sql` to deactivate only the known built-in demo hashes, then create a new administrator through the CLI before public access. This migration does not delete users or other application data.

Create a private SQL backup from the running Compose database:

```sh
./scripts/backup-db.sh
```

Restore a backup into the configured database (the script requires typing `RESTORE`):

```sh
./scripts/restore-db.sh backups/ift-result-tracking-YYYYMMDD-HHMMSS.sql
```

Backups are written under `backups/` with owner-only permissions and are excluded from Git and Docker build context. Store an additional copy outside the VM for disaster recovery.

## Host-level Nginx reverse proxy

Keep the existing host Nginx. Set `APP_URL` to the public HTTPS origin in `.env`, then proxy the virtual host to the loopback-only application port. For example, inside the existing TLS server block:

```nginx
location / {
    proxy_pass http://127.0.0.1:8080;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
}
```

Keep TLS termination and public access controls at the host Nginx. Do not publish the database port or remove the app's loopback-only port binding.

## Security and operations

- Use unique, randomly generated database and root passwords. `.env` is ignored by Git and excluded from the image.
- Create staff accounts through the CLI. The database seed no longer installs publicly known demo credentials.
- Set `APP_ENV=production`; PHP error details are disabled in the production image and session cookies are HTTP-only, SameSite, and secure.
- The app image copies only the app code, CLI setup command, Composer dependencies, and public web files. `app/` is above Apache's document root and denies direct access in local Apache deployments.
- MariaDB is isolated on an internal Docker network and persists to the named `database_data` volume.
- Run `docker compose logs -f app db` to inspect container logs. Application health returns only `ok` or `unavailable`, without connection details.
