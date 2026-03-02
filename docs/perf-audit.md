# Perf audit playbook (Postgres)

Цей документ — «шпаргалка» для швидкої перевірки, що **публічні сторінки** не деградують по перфомансу і не з’являються **N+1** після змін.

Сторінки, які мають бути стабільними:
- `/catalog`
- `/providers/{slug}`

> TL;DR: після змін у фільтрах/відношеннях/контролерах проганяємо тести, а для впевненості — робимо 2–3 `EXPLAIN (ANALYZE, BUFFERS)` на типових запитах.

---

## 1) Мінімальний чек-лист після змін

1) **Тести** (як мінімум feature):
   - `php artisan test --testsuite=Feature`
   - (або весь suite: `php artisan test`)
2) Перевірити guard-и на N+1:
   - `tests/Feature/CatalogTest.php::test_catalog_page_does_not_trigger_n_plus_one_queries`
   - `tests/Feature/ProviderShowTest.php::test_provider_page_does_not_trigger_n_plus_one_queries`
3) Якщо змінювали SQL/індекси/фільтри — зробити `EXPLAIN` для 1–2 кейсів каталогу + 1 кейса provider show.

> Швидкий варіант (є в репозиторії):
>
> ```bash
> php artisan perf:audit
> php artisan perf:audit --explain
> php artisan perf:audit --analyze
> php artisan perf:audit --provider=demo-provider
> # Catalog-only variants:
> php artisan perf:audit --city=ки
> php artisan perf:audit --price_from=100 --include_no_price=1
> ```
>
> Команда друкує SQL (та bindings), а з `--explain/--analyze` — також план виконання (Postgres).

---

## 2) Як швидко отримати SQL з Laravel


> Примітка: якщо у вашій системі немає `rg` (ripgrep), використовуйте стандартний `grep -R` для пошуку по репозиторію. Наприклад:
>
> ```bash
> grep -R "ProviderController" -n app/
> ```


Для разового аналізу (dev) можна тимчасово увімкнути лог SQL:

```bash
php artisan tinker
```

```php
use Illuminate\Support\Facades\DB;

DB::listen(fn ($q) => dump($q->sql, $q->bindings));

// Далі відкрийте потрібну сторінку у браузері або викличте код, який будує query.
```

Після того як отримали SQL → запускаємо його у `psql` з `EXPLAIN (ANALYZE, BUFFERS)`.

### 2.1 Швидкий запуск psql через Sail

> Підійде, якщо у вас Postgres у docker-compose (Laravel Sail).

```bash
./vendor/bin/sail psql -U "${DB_USERNAME:-sail}" -d "${DB_DATABASE:-laravel}" -h "${DB_HOST:-pgsql}" -p "${DB_PORT:-5432}"
```

Далі вставляємо запит з префіксом:

```sql
EXPLAIN (ANALYZE, BUFFERS)
-- ваш SELECT ...
;
```

> Якщо у проєкті використовується інша назва сервісу БД або інші креденшали — звірте `.env` / `docker-compose.yml`.

---

## 3) EXPLAIN шаблони (catalog)

> Примітка: це **спрощені** запити, які повторюють ідею реальних фільтрів.
> Важливо, щоб planner використовував індекси і не робив зайвих `Seq Scan` на великих таблицях.

### 3.1 Catalog: базовий лістинг (newest)

```sql
EXPLAIN (ANALYZE, BUFFERS)
SELECT o.id
FROM offers o
JOIN business_profiles bp ON bp.id = o.business_profile_id
WHERE o.is_active = true
  AND bp.is_active = true
ORDER BY o.created_at DESC
LIMIT 20;
```

Очікуємо:
- індекс під `(is_active, created_at)` або схожий (може бути `Bitmap Index Scan`)
- без `Seq Scan` по `offers` при великій таблиці

### 3.2 Catalog: prefix city (case-insensitive)

```sql
EXPLAIN (ANALYZE, BUFFERS)
SELECT o.id
FROM offers o
JOIN business_profiles bp ON bp.id = o.business_profile_id
WHERE o.is_active = true
  AND bp.is_active = true
  AND lower(bp.city) LIKE 'ки%' ESCAPE '!'
ORDER BY o.created_at DESC
LIMIT 20;
```

Очікуємо:
- використання expression index на `lower(city)` з `text_pattern_ops` (або еквівалент)

### 3.3 Catalog: price range (+ include no price)

```sql
-- price_from/price_to логіка в Laravel складніша,
-- але базова ідея: (price_from/price_to) + include_no_price.

EXPLAIN (ANALYZE, BUFFERS)
SELECT o.id
FROM offers o
JOIN business_profiles bp ON bp.id = o.business_profile_id
WHERE o.is_active = true
  AND bp.is_active = true
  AND (
    (o.price_from IS NOT NULL AND o.price_from >= 100)
    OR (o.price_from IS NULL AND o.price_to IS NULL)
  )
ORDER BY o.created_at DESC
LIMIT 20;
```

Очікуємо:
- індекси під `price_from/price_to` (або часткові/композитні), щоб не було повного скану

---

## 4) EXPLAIN шаблони (provider show)

### 4.1 Provider show: offers list

```sql
EXPLAIN (ANALYZE, BUFFERS)
SELECT o.id
FROM offers o
JOIN business_profiles bp ON bp.id = o.business_profile_id
WHERE bp.slug = 'demo-provider'
  AND bp.is_active = true
  AND o.is_active = true
ORDER BY o.created_at DESC
LIMIT 12;
```

Очікуємо:
- індекс, що підтримує фільтр по `business_profile_id` + `is_active` + `created_at`
- швидкий lookup `bp.slug` (індекс на `business_profiles.slug`)

### 4.2 Provider show: latest portfolio posts (published)

```sql
EXPLAIN (ANALYZE, BUFFERS)
SELECT pp.id
FROM portfolio_posts pp
JOIN business_profiles bp ON bp.id = pp.business_profile_id
WHERE bp.slug = 'demo-provider'
  AND pp.published_at IS NOT NULL
  AND pp.published_at <= now()
ORDER BY pp.published_at DESC
LIMIT 6;
```

Очікуємо:
- індекс на `(business_profile_id, published_at)` або еквівалент

---

## 5) Коли достатньо тестів, а коли треба EXPLAIN

**Достатньо тестів**, якщо:
- зміни були лише у UI (Vue) або копірайт/дрібний refactor без змін query

**EXPLAIN бажано**, якщо:
- змінювали фільтри каталогу, `whereHas`, `with()/withCount()`, eager loading
- додавали/міняли індекси
- помітили збільшення кількості SQL-запитів у N+1 тестах

---

## 6) Де ще дивитись

- `docs/testing-db.md` — як влаштована тестова схема `testing` і базові підказки
- Feature тести каталогу/провайдера — як «золота сітка» проти регресій

---

## 7) Примітка про індекси для provider show (stories/portfolio posts)

Для публічної сторінки провайдера критичні запити по:
- `stories` (активні: `expires_at > now()`)
- `portfolio_posts` (опубліковані: `published_at <= now()`)

У репозиторії є міграція, яка **фіксує явні назви** композитних індексів (і при цьому намагається не падати при `migrate:fresh`), див.:
- `database/migrations/2026_03_02_133117_add_provider_public_page_indexes_to_stories_and_portfolio_posts.php`

Чому це важливо:
- Laravel для індексів без явного імені генерує auto-name на кшталт `stories_business_profile_id_expires_at_index`.
- Якщо пізніше захочемо підтримувати `dropIndex()` у `down()` або стандартизувати імена — краще мати стабільні, явні назви.
