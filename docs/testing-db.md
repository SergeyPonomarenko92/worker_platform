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
