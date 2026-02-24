export const offerTypeLabel = (type, { plural = false } = {}) => {
  if (plural) {
    switch (type) {
      case 'service':
        return 'Послуги'
      case 'product':
        return 'Товари'
      default:
        return type
    }
  }

  switch (type) {
    case 'service':
      return 'Послуга'
    case 'product':
      return 'Товар'
    default:
      return type
  }
}

export const formatNumber = (value) => {
  if (value === null || value === undefined || value === '') return ''
  const num = Number(value)
  if (Number.isNaN(num)) return String(value)
  return new Intl.NumberFormat('uk-UA').format(num)
}

export const formatPrice = (offer) => {
  const from = offer?.price_from
  const to = offer?.price_to
  const currency = offer?.currency || 'UAH'

  const hasFrom = from !== null && from !== undefined
  const hasTo = to !== null && to !== undefined

  if (!hasFrom && !hasTo) return 'ціна за домовленістю'
  if (hasFrom && hasTo) return `${formatNumber(from)} — ${formatNumber(to)} ${currency}`
  if (hasFrom) return `від ${formatNumber(from)} ${currency}`
  return `до ${formatNumber(to)} ${currency}`
}

export const normalizeWebsite = (raw) => {
  const v = String(raw || '').trim()
  if (!v) return ''
  if (v.startsWith('http://') || v.startsWith('https://')) return v
  return `https://${v}`
}

export const formatAvgRatingUk = (avg) => {
  if (avg === null || avg === undefined) return ''
  const rounded = Math.round(Number(avg) * 10) / 10
  if (Number.isNaN(rounded)) return ''
  return String(rounded).replace('.', ',')
}
