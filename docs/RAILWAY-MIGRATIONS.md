# Running Migrations on Railway

Migrations need to run in an environment that can reach your PostgreSQL database. Railway’s proxy host (e.g. `mainline.proxy.rlwy.net:23235`) is only reachable when network access to Railway is available.

## Option 1: Migrations on deploy (recommended)

Run migrations as part of your start/release command so every deploy applies pending migrations.

**Example start command:**

```bash
php artisan migrate --force && php artisan config:cache && php-fpm
```

Or use a separate **release command** in Railway (if your stack supports it) so migrations run before the new app process starts.

**Required:** Set `DB_URL` (or `DATABASE_URL`) on the Laravel service to your Postgres URL, e.g.:

- Railway injects this when you add the Postgres **variable reference** from the PostgreSQL service into the Laravel service.
- Or set it manually:  
  `postgresql://USER:PASSWORD@mainline.proxy.rlwy.net:23235/railway`  
  (use the exact host, port, user, password, and database from your Railway Postgres service.)

The app uses `DB_URL` first, then falls back to `DATABASE_URL`, so either name works.

## Option 2: One-off run from your machine

From a terminal that has network access (e.g. your laptop, not a sandbox that blocks outbound connections):

```bash
cd /path/to/freedom-stack
export DB_URL="postgresql://USER:PASSWORD@mainline.proxy.rlwy.net:23235/railway"
php artisan migrate --force
```

Use the real user, password, and database name from the Railway Postgres dashboard (Variables or Connect tab).

## Option 3: Railway CLI

With [Railway CLI](https://docs.railway.app/develop/cli) linked to your project:

```bash
railway run php artisan migrate --force
```

This runs the command in Railway’s environment, so it uses the same `DB_URL` / `DATABASE_URL` as your deployed app and can reach the database.

## Why “migration failing” from Cursor/sandbox?

If you run `php artisan migrate` from Cursor (or another sandbox) and see:

`could not translate host name "mainline.proxy.rlwy.net" to address`

then the environment where the command runs has **no network access** to Railway’s proxy. The migration files and app code are fine; run the same command using one of the options above (deploy, your machine with `DB_URL`, or `railway run`).
