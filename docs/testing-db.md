# Testing DB (Postgres)

У проекті тести запускаються на **Postgres** з окремим `search_path`, щоб не змішувати дані dev/тестів.

Де це налаштовано:
- `phpunit.xml`:
  - `DB_CONNECTION=pgsql`
  - `DB_SEARCH_PATH=testing`

Laravel (`config/database.php`) підхоплює `DB_SEARCH_PATH` як `search_path` для pgsql.

## 1) Підготовка схеми `testing`

> Потрібно зробити **один раз** для вашої БД.

### Варіант A: локальний Postgres

Підключіться до БД `worker_platform` і створіть schema:

```sql
CREATE SCHEMA IF NOT EXISTS testing;
GRANT USAGE, CREATE ON SCHEMA testing TO worker;
ALTER ROLE worker IN DATABASE worker_platform SET search_path TO public;
```

(Опційно) Якщо хочете заборонити випадкові записи тестів у `public`, не давайте прав на `public` schema.

### Варіант B: Sail (контейнер pgsql)

Відкрийте psql всередині контейнера:

```bash
./vendor/bin/sail psql
```

Далі виконайте ті ж SQL-команди, але з вашим користувачем/БД із Sail.

## 2) Запуск тестів

### Без Sail

```bash
composer test
```

### Через Sail

```bash
./vendor/bin/sail test
```

## 3) Як це працює

- Під час тестів Laravel підключається до тієї ж БД, але з `search_path=testing`.
- Міграції в тестах створюють таблиці **у схемі `testing`**.
- Це зменшує шанс зіпсувати dev-дані в `public`.

Якщо бачите помилки типу `schema "testing" does not exist` — значить schema не створена або немає прав.

## 4) Perf-аудит: EXPLAIN (індекси, N+1)

Ціль: періодично перевіряти, що основні публічні сторінки (`/catalog`, `/providers/{slug}`) не деградують по перфомансу та не ловлять N+1.

### 4.1 Швидкий EXPLAIN для каталогу

У `psql` (або через будь-який SQL-клієнт) запустіть EXPLAIN для запиту, який схожий на реальний:

```sql
EXPLAIN (ANALYZE, BUFFERS)
SELECT o.id
FROM offers o
JOIN business_profiles bp ON bp.id = o.business_profile_id
WHERE o.is_active = true
  AND bp.is_active = true
  AND (lower(bp.city) LIKE 'київ%' ESCAPE '!')
ORDER BY o.created_at DESC
LIMIT 20;
```

На що дивимось:
- **Index Scan / Bitmap Index Scan** по індексах `offers` (active/created_at/price) та по індексу міста `business_profiles`.
- відсутність `Seq Scan` на великих таблицях при типових фільтрах.

> Примітка: конкретний SQL у Laravel складніший (фільтри + whereHas), але принцип той самий: перевіряємо, що індекси покривають реальні WHERE/ORDER BY.

### 4.2 Перевірка N+1 на рівні PHP

У тестах вже є guard на кількість запитів для сторінки провайдера:
- `tests/Feature/ProviderShowTest.php::test_provider_page_does_not_trigger_n_plus_one_queries`

Якщо робите зміни у `ProviderController@show` або звʼязках моделей — проганяйте цей тест і не занижуйте query-budget без потреби.

### 4.3 Як швидко побачити SQL з Laravel

Для разового аналізу (dev) можна тимчасово увімкнути лог SQL у `tinker`:

```bash
php artisan tinker
```

```php
DB::listen(fn ($q) => dump($q->sql, $q->bindings));

// Потім відкрийте потрібну сторінку у браузері, або викличте код, який будує query.
```

Після того, як ви отримали SQL, перенесіть його у `psql` і проганяйте через `EXPLAIN (ANALYZE, BUFFERS)`.
