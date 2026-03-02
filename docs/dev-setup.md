# Dev setup

## 1) Requirements
- Docker + Docker Compose (recommended via Laravel Sail)
- or: PHP 8.2+, Composer, Node + npm, Postgres (recommended) or MySQL

### Optional (quality of life)
- `ripgrep` (`rg`) for fast code search (docs sometimes reference it). If you don't have it, you can always use `grep -R`.
  - Ubuntu/Debian: `sudo apt-get install ripgrep`
- `psql` client for quick `EXPLAIN (ANALYZE, BUFFERS)` checks when doing perf work.

## 2) Install
```bash
composer install
cp .env.example .env
php artisan key:generate

npm install
```

## 3) Run with Sail (recommended)
> Use Sail to avoid local PHP extensions / DB differences.

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
./vendor/bin/sail npm run dev
```

Open:
- http://localhost/catalog

### Run tests (Sail)
```bash
./vendor/bin/sail test
```

> Tests use Postgres `search_path=testing`. If you hit DB/schema issues, see `docs/testing-db.md`.

## 4) Run without Sail (local)
### Database
#### Option A: Postgres (recommended)
Create DB/user:
- db: `worker_platform`
- user: `worker`
- pass: `worker`

Update `.env` accordingly.

Run:
```bash
php artisan migrate
php artisan db:seed
```

#### Option B: MySQL
Configure `.env` and run the same migrate/seed commands.

### Run
```bash
npm run dev
php artisan serve
```

Open:
- http://localhost:8000/catalog

## 5) Demo accounts (seed)
- client@example.com / password
- provider@example.com / password
