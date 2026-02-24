# Next steps

## Changelog (коротко)
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
- 2026-02-24: Catalog — у картках офферів user-friendly лейбли типу (Послуга/Товар) + форматування ціни (range/від/до/«за домовленістю»).

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
1) Stage 5 (Polish):
   - [x] user-friendly лейбли у каталозі (тип service/product → Послуга/Товар) + форматування цін (range `price_from`/`price_to`, `ціна за домовленістю`)
   - [ ] фільтр по категоріях з деревом (parent/child) або пошук по категорії (в роботі: додано дерево в select у каталозі)
2) Техборг:
   - пройтись по індексах (offers/business_profiles) і додати/підтвердити потрібні під реальні запити
