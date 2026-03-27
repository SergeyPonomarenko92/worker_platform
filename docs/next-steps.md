# Next steps

## Changelog (коротко)
- 2026-03-27: Catalog — category suggestions: eager-load ancestors to avoid N+1 when building deep category paths + regression test.
- 2026-03-27: Catalog — додано JSON endpoint для підказок міст (autocomplete): `GET /catalog/cities?q=...` + feature tests.
- 2026-03-27: Catalog UI — підказки міст тепер використовують `GET /catalog/cities` (узгоджено з новим endpoint).
- 2026-02-12: Stage 3 (Content) — CRUD PortfolioPost/Story у кабінеті + фільтрація портфоліо на public provider page + статуси в UI.
- 2026-02-12: Tests — додано feature happy-path тести для PortfolioPost/Story.
- 2026-02-12: Stage 4 (Deals) — додано кабінет угод (створення вручну + зміна статусів) + тести.
- 2026-02-16: UX/Polish — `/` редіректить на `/catalog`; форми показують помилки валідації (Inertia, без нативної HTML-валидації); локаль UI/validation = `uk`.
- 2026-02-16: Stories — у кабінеті протерміновані історії приховані за замовчуванням (перемикач `?show_expired=1`).
- 2026-02-16: Deals — у UI кнопка «Скасувати» вимкнена для завершених угод (узгоджено з бекенд-правилами).
- 2026-02-23: Catalog — фільтри/сортування UX (debounce, пагінація з номерами, чіпи активних фільтрів).
- 2026-02-23: Catalog — price range filter + опція включати оффери без ціни.
- 2026-02-23: Catalog — оптимізація city filter (prefix search + індекс `lower(city)`).
- 2026-02-23: Tests/Perf — тести на фільтри каталогу + індекси для `offers`.
- 2026-02-24: Catalog/Provider/Cabinet — великий пакет polish+tests+perf:
  - Catalog: лейбли типу (Послуга/Товар) + форматування ціни (range/від/до/«за домовленістю») + лейбли валют.
  - Catalog: дерево категорій у select + фільтр категорії включає descendants (child/grandchild) + у UI показується повний шлях категорії (parent → child).
  - Catalog: UX/robustness: нормалізація пробілів у `q/city`, пошук `q` також матчитиме `BusinessProfile.name`, `include_no_price` disabled без price bounds, невалідний `sort` ігнорується (fallback на newest, без validation errors).
  - Catalog: perf — select тільки потрібні колонки + додано індекс під price.
  - Provider public page: оффери показують category+форматовану ціну; додано статистику (offers/reviews/avg rating); зовнішні лінки з `target=_blank` мають `rel=noopener`.
  - Provider cabinet: у списку профілів бейдж Активний/Неактивний + лінк на публічну сторінку.
  - Tests: суттєво розширено покриття фільтрів (ціна/include_no_price, дерево категорій, sort edge-cases).
  - Offers: валідація дозволяє `price_to` без `price_from`.
- 2026-02-25: Stage 5 polish (Catalog/Provider/Auth/a11y):
  - Catalog:
    - reset disabled без активних фільтрів + підказки/tooltip-и; helper-hint для ціни.
    - чіпи: обрізання довгих значень (q/city/provider/category) + повні значення в tooltip.
    - фільтр `provider` (slug) + нормалізація (можна вставляти URL `/providers/{slug}` — slug витягується).
    - пагінація: `aria-current`, коректний focus-visible, disabled елементи без “порожніх” лінків.
    - карточка оффера: імʼя провайдера — лінк на `/providers/{slug}`.
  - Provider public page:
    - CTA/UX для секцій (відгуки/пропозиції/портфоліо): “показати всі”, `?all_offers=1`, `?all_portfolio=1`, збереження `all_*` query params.
    - anchors для секцій (portfolio/offers/reviews).
    - контакти: телефон `tel:` + адреса/місто → Google Maps (noopener).
    - robustness для eligibleDealId (ігноруємо completed без `completed_at`, guard по часу).
    - perf: зменшено дефолтне preload портфоліо (повне — через `all_portfolio`).
  - Auth/UI/a11y:
    - локалізація Forgot Password (uk).
    - focus-visible rings для ключових CTA/лінків; flash messages озвучуються скрінрідерами.
  - UI: `formatNumber()` коректно парсить числа з пробілами/NBSP/апострофами.
  - Tests: додано/підтягнуто покриття (provider show preload limits, all_portfolio behavior, deals offer belongs to BP тощо).
- 2026-02-26: Techborg/Perf — додано Postgres індекс для prefix-пошуку міста: `lower(city) LIKE 'ки%'` (expression index + `text_pattern_ops`).
- 2026-02-26: Catalog polish/tests/a11y/robustness:
  - Provider filter: винесено нормалізацію в `App\Support\QueryParamNormalizer::providerSlug()` + unit/feature тести (підтримка full URL / `/providers/{slug}` path, query/hash, пробіли, trailing slashes, upper-case).
  - Додано `QueryParamNormalizer::text()` (trim + collapse whitespace + NBSP) і застосовано для `q/city`.
  - Catalog UX: показ кількості результатів, нормалізація інпутів на blur, уникнення double-submit, покращена сітка фільтрів + підказки.
  - Catalog a11y: `role="search"`/`fieldset` для фільтрів, покращені active-filter chips (семантика + доступність кнопки видалення).
  - Robustness: екранування спецсимволів (`%`, `_`, `!`) у `city` prefix LIKE.
  - BusinessProfile: нормалізація `phone` (trim, пусте → null).
  - Reviews: graceful handling повторної відправки відгуку.
  - Perf: індекс для лістингу офферів у профілі (bp, active, created_at).

- 2026-03-03: Micro-polish / robustness / tests (серія невеликих безпечних змін):
  - Contact fields: посилено нормалізацію/валідацію website/URL/phone (trim unicode whitespace, обробка «порожніх» значень, trim пунктуації для доменів) + unit/feature тести.
  - Offers: allowlist для `currency` (`UAH|USD|EUR`) + нормалізація в `OfferFormRequest` + feature-тест.
  - Catalog/UI: `formatNumber()` у фронтенді тепер прибирає також thin unicode spaces (`\u202F`, `\u2009`).
  - Sitemap: `catalog <lastmod>` узгоджено з фактично видимими офферами та оновленнями активних провайдерів + feature-тести.
  - A11y/UX: дрібні покращення в кабінеті (aria-label/title для action links, autocomplete hints у формі BusinessProfile).
  - Tech-debt/tests: cleanup дубльованих тестів, додаткове покриття `QueryParamNormalizer::providerSlug()`, невеликий refactor констант preload у `ProviderController`.

- 2026-03-06: A11y/semantics + test cleanup + regression tests:
  - A11y/UX (публічні сторінки):
    - Catalog/Index.vue і Providers/Show.vue: обернуто контент у `<main>` landmark (раніше відсутній на публічних сторінках — screen readers не могли знайти основний контент).
    - Offer cards у каталозі та на сторінці провайдера: замінено `<div>` → `<article>` для кращої семантики.
    - Portfolio posts і reviews: дати обернуті в `<time datetime="...">` для SEO та screen readers.
  - Test cleanup (CatalogTest):
    - Об'єднано дублікатні тести нормалізації whitespace у city/q фільтрах у спільні data providers (regular + unicode whitespace в одному тесті).
  - Regression тести (edge-cases normalizers):
    - `QueryParamNormalizerTest`: `unsignedInt` з BOM, zero-width space, LTR mark, soft hyphen.
    - `BusinessProfileRequestNormalizerTest`: `countryCode` з невидимими unicode-символами навколо літер.
    - `ContactFieldNormalizerTest`: phone з unicode whitespace без цифр; website з BOM/LTR mark.

## Current status
- Branch: `main`
- Postgres configured and working locally.
- Migrations and seeders run successfully.
- Pages working: `/` (→ redirect to `/catalog`), `/catalog`, `/providers/demo-provider`.
- Provider cabinet pages added (multiple business profiles):
  - `/dashboard/business-profiles` (list)
  - `/dashboard/business-profiles/create` (create)
  - `/dashboard/business-profiles/{businessProfile}/edit` (edit)
  - `/dashboard/business-profiles/{businessProfile}/offers` (offers CRUD)
  - `/dashboard/business-profiles/{businessProfile}/portfolio-posts` (portfolio posts CRUD)
  - `/dashboard/business-profiles/{businessProfile}/stories` (stories CRUD; expired hidden by default)
  - `/dashboard/business-profiles/{businessProfile}/deals` (deals cabinet)
- Reviews flow: `/deals/{deal}/review/create` (client leaves review after completed deal)

## Done
- [x] Stage 2: Provider cabinet (CRUD)
  - [x] BusinessProfile CRUD (create/edit)
  - [x] Offer CRUD (service|product)
  - [x] Authorization: only owner can edit their profile/offers
- [x] Add basic navigation links in UI (Dashboard → Provider cabinet)
- [x] Add feature tests for provider cabinet authorization + basic happy paths
- [x] Fix OfferPolicy logic to not rely on unloaded relationships (avoid false-deny on create)
- [x] Update docs/dev-setup.md to include Sail run + tests commands
- [x] Localize provider cabinet UI copy to Ukrainian (strings on new pages + nav)
- [x] Add success/error flash messages (Inertia shared props) + show them in AuthenticatedLayout
- [x] Support multiple BusinessProfiles per user: add profiles list + scope offers under selected business profile

## Done
- [x] Merge `feature/domain-models` → `main` (Serj via GitKraken).

## Done
- Stage 3 (Content): Provider cabinet pages/routes/policies for:
  - PortfolioPost (list/create/edit/delete)
  - Story (list/create/edit/delete)
- Public provider page: portfolio posts filtered by `published_at`:
  - `published_at` is not null
  - `published_at <= now()`

## TODO (next session)
1) Функціональні покращення (наступний етап — після MVP polish):
   - [ ] Пошук/фільтрація: автокомпліт міст (підказки з існуючих міст у базі).
   - [ ] Пошук/фільтрація: автокомпліт категорій (fuzzy match по назві).
   - [ ] Повідомлення: email-нотифікація клієнту при створенні угоди провайдером.
   - [ ] Зображення: upload фото для PortfolioPost та Story (storage + thumbnails).
   - [ ] Модерація: базова модерація контенту (flag/report).
2) UI/UX (наступний етап):
   - [ ] Responsive: перевірити адаптивність на мобільних (catalog cards, provider page).
   - [ ] Dark mode: підготувати dark variant (Tailwind `dark:` classes).
   - [ ] Breadcrumbs: додати breadcrumb навігацію у кабінет (dashboard → business-profiles → offers).
3) Tech-debt:
   - [ ] Feature tests: додати E2E тести для повного user flow (register → create profile → create offer → deal → review).
   - [ ] API: підготувати api.php routes для майбутнього мобільного клієнта.
   - [x] CI: налаштовано GitHub Actions (`.github/workflows/ci.yml`) — Pint + build assets + міграції у schema `testing` (Postgres service) + `php artisan test`.
