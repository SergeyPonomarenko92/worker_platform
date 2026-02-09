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
- [x] Open PR: `feature/domain-models` → `main` and merge after review. *(Merged via GitKraken, no GitHub PR.)*
- [x] Stage 2: Provider cabinet (CRUD)
  - [x] BusinessProfile CRUD (create/edit)
  - [x] Offer CRUD (service|product)
  - [x] Authorization: only owner can edit their profile/offers
- [x] Add basic navigation links in UI (Dashboard → Provider cabinet)

## TODO (next session)
1) Add feature tests for provider cabinet authorization + basic happy paths:
   - only owner can edit BusinessProfile
   - only owner can edit/delete Offer
2) Double-check OfferPolicy logic to not rely on unloaded relationships (avoid false-deny on create).
