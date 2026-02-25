/**
 * Build URL for provider public page with "load all" query params.
 *
 * We keep params explicit and stable (all_* = 1) so we can preserve
 * the state of other sections (offers/reviews/portfolio) while navigating.
 */
export function providerShowUrl(
  slug,
  { all_offers = false, all_portfolio = false, all_reviews = false, hash = null } = {}
) {
  const params = new URLSearchParams()

  if (all_offers) params.set('all_offers', '1')
  if (all_portfolio) params.set('all_portfolio', '1')
  if (all_reviews) params.set('all_reviews', '1')

  const qs = params.toString()
  const url = qs ? `/providers/${slug}?${qs}` : `/providers/${slug}`

  if (!hash) return url

  const normalized = String(hash).replace(/^#/, '')
  return `${url}#${encodeURIComponent(normalized)}`
}
