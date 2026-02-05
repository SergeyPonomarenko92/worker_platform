# Dev setup

## 1) Requirements
- PHP 8.2+
- Composer
- Node + npm
- Postgres (recommended) or MySQL

## 2) Install
```bash
composer install
cp .env.example .env
php artisan key:generate

npm install
```

## 3) Database
### Option A: Postgres (recommended)
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

### Option B: MySQL
Configure `.env` and run the same migrate/seed commands.

## 4) Run
```bash
npm run dev
php artisan serve
```

Open:
- http://localhost:8000/catalog

## 5) Demo accounts (seed)
- client@example.com / password
- provider@example.com / password
