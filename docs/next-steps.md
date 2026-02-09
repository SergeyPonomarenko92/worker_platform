# Next steps

## Current status
- Branch: `feature/domain-models`
- Postgres configured and working locally.
- Migrations and seeders run successfully.
- Pages working: `/catalog`, `/providers/demo-provider`.
- Provider cabinet pages added:
  - `/dashboard/business-profile` (create/edit)
  - `/dashboard/offers` (CRUD)

## Done
- [x] Stage 2: Provider cabinet (CRUD)
  - [x] BusinessProfile CRUD (create/edit)
  - [x] Offer CRUD (service|product)
  - [x] Authorization: only owner can edit their profile/offers
- [x] Add basic navigation links in UI (Dashboard → Provider cabinet)
- [x] Add feature tests for provider cabinet authorization + basic happy paths:
  - [x] only owner can edit BusinessProfile
  - [x] only owner can edit/delete Offer
- [x] Fix OfferPolicy logic to not rely on unloaded relationships (avoid false-deny on create)
- [x] Update docs/dev-setup.md to include Sail run + tests commands

## TODO (next session)
1) Merge `feature/domain-models` → `main` (Serj does via GitKraken).
2) (Optional) Localize provider cabinet UI copy to Ukrainian (strings on new pages + nav).
