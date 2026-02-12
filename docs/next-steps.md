# Next steps

## Changelog (коротко)
- 2026-02-12: Stage 3 (Content) — CRUD PortfolioPost/Story у кабінеті + фільтрація портфоліо на public provider page + статуси в UI.

## Current status
- Branch: `main`
- Postgres configured and working locally.
- Migrations and seeders run successfully.
- Pages working: `/catalog`, `/providers/demo-provider`.
- Provider cabinet pages added (multiple business profiles):
  - `/dashboard/business-profiles` (list)
  - `/dashboard/business-profiles/create` (create)
  - `/dashboard/business-profiles/{businessProfile}/edit` (edit)
  - `/dashboard/business-profiles/{businessProfile}/offers` (offers CRUD)
  - `/dashboard/business-profiles/{businessProfile}/portfolio-posts` (portfolio posts CRUD)
  - `/dashboard/business-profiles/{businessProfile}/stories` (stories CRUD)

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
1) Finish Stage 3 polish:
   - add more feature tests for PortfolioPost/Story (happy path create/update/delete)
   - verify UI in browser (links, flash messages, validations)
2) Public provider page: ensure stories show only active (already filtered by `expires_at > now()`), and decide whether to hide expired stories in cabinet or show with status (now shows status).
3) Stage 4 (Deals + Reviews): start CRUD/flows.
