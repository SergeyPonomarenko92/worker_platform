<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, reactive, watch } from 'vue'
import EmptyStateCard from '@/Components/EmptyStateCard.vue'
import { offerTypeLabel, formatNumber, formatPrice } from '@/lib/formatters'

const props = defineProps({
  offers: Object,
  filters: Object,
  categories: Array,
})

const form = reactive({
  q: props.filters?.q || '',
  type: props.filters?.type || '',
  category_id: props.filters?.category_id || '',
  city: props.filters?.city || '',
  provider: props.filters?.provider || '',
  price_from: props.filters?.price_from ?? '',
  price_to: props.filters?.price_to ?? '',
  include_no_price: props.filters?.include_no_price || false,
  sort: props.filters?.sort || 'newest',
})

function normalizeProviderInputToSlug(value) {
  const raw = String(value ?? '').trim()
  if (!raw) return ''

  // Allow pasting a full provider URL.
  const m = raw.match(/\/providers\/([^/?#]+)/i)
  const slug = m?.[1] ? m[1] : raw

  return String(slug).trim().toLowerCase()
}

function submit() {
  router.get(
    '/catalog',
    {
      q: (form.q || '').trim() || undefined,
      type: form.type || undefined,
      category_id: form.category_id || undefined,
      city: (form.city || '').trim() || undefined,
      provider: normalizeProviderInputToSlug(form.provider) || undefined,
      price_from: String(form.price_from).trim() || undefined,
      price_to: String(form.price_to).trim() || undefined,
      include_no_price:
        form.include_no_price && (String(form.price_from || '').trim() || String(form.price_to || '').trim())
          ? 1
          : undefined,
      sort: form.sort || undefined,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      only: ['offers', 'filters'],
    },
  )
}

const typeLabel = (type) => offerTypeLabel(type, { plural: true })

const sortLabel = (sort) => {
  switch (sort) {
    case 'newest':
      return 'Найновіші'
    case 'price_asc':
      return 'Ціна: зростання'
    case 'price_desc':
      return 'Ціна: спадання'
    default:
      return sort
  }
}

const flattenCategories = (nodes, depth = 0, parentLabel = '') => {
  const out = []
  ;(nodes || []).forEach((n) => {
    const label = parentLabel ? `${parentLabel} → ${n.name}` : n.name
    out.push({ id: n.id, name: n.name, depth, label })
    if (n.children?.length) out.push(...flattenCategories(n.children, depth + 1, label))
  })
  return out
}

const flatCategories = computed(() => flattenCategories(props.categories || []))

const categoryLabel = (id) => flatCategories.value.find((c) => String(c.id) === String(id))?.label || 'Категорія'

const providerSlug = computed(() => normalizeProviderInputToSlug(form.provider))

const chipValue = (value) => String(value ?? '').trim()

const chipDisplayValue = (value, max = 30) => {
  const trimmed = chipValue(value)
  if (!trimmed) return ''
  if (trimmed.length <= max) return trimmed
  return `${trimmed.slice(0, max)}…`
}

const activeChips = computed(() => {
  const chips = []

  if (chipValue(form.q)) {
    chips.push({
      key: 'q',
      label: `Пошук: ${chipDisplayValue(form.q)}`,
      title: `Пошук: ${chipValue(form.q)}`,
    })
  }

  if (form.type) chips.push({ key: 'type', label: `Тип: ${typeLabel(form.type)}` })

  if (form.category_id) {
    const full = categoryLabel(form.category_id)

    chips.push({
      key: 'category_id',
      label: `Категорія: ${chipDisplayValue(full)}`,
      title: `Категорія: ${full}`,
    })
  }

  if (chipValue(form.city)) {
    chips.push({
      key: 'city',
      label: `Місто: ${chipDisplayValue(form.city)}`,
      title: `Місто: ${chipValue(form.city)}`,
    })
  }

  if (chipValue(form.provider)) {
    chips.push({
      key: 'provider',
      label: `Провайдер: ${chipDisplayValue(form.provider)}`,
      title: `Провайдер: ${chipValue(form.provider)}`,
    })
  }

  if (String(form.price_from || '').trim()) chips.push({ key: 'price_from', label: `Ціна від: ${formatNumber(String(form.price_from).trim())}` })
  if (String(form.price_to || '').trim()) chips.push({ key: 'price_to', label: `Ціна до: ${formatNumber(String(form.price_to).trim())}` })
  if (form.include_no_price && (String(form.price_from || '').trim() || String(form.price_to || '').trim())) {
    chips.push({ key: 'include_no_price', label: 'Включати «за домовленістю»' })
  }
  if (form.sort && form.sort !== 'newest') chips.push({ key: 'sort', label: `Сортування: ${sortLabel(form.sort)}` })

  return chips
})

const hasPriceBounds = computed(() => Boolean(String(form.price_from || '').trim() || String(form.price_to || '').trim()))

const hasActiveFilters = computed(() => activeChips.value.length > 0)

function clearChip(key) {
  if (key === 'q') form.q = ''
  if (key === 'type') form.type = ''
  if (key === 'category_id') form.category_id = ''
  if (key === 'city') form.city = ''
  if (key === 'provider') form.provider = ''
  if (key === 'price_from') form.price_from = ''
  if (key === 'price_to') form.price_to = ''
  if (key === 'include_no_price') form.include_no_price = false
  if (key === 'sort') form.sort = 'newest'

  submit()
}

let qDebounceTimer = null
let cityDebounceTimer = null
let providerDebounceTimer = null
let priceDebounceTimer = null

watch(
  () => form.q,
  () => {
    if (qDebounceTimer) clearTimeout(qDebounceTimer)
    qDebounceTimer = setTimeout(() => submit(), 400)
  },
)

watch(
  () => form.city,
  () => {
    if (cityDebounceTimer) clearTimeout(cityDebounceTimer)
    cityDebounceTimer = setTimeout(() => submit(), 400)
  },
)

watch(
  () => form.provider,
  () => {
    if (providerDebounceTimer) clearTimeout(providerDebounceTimer)
    providerDebounceTimer = setTimeout(() => submit(), 400)
  },
)

watch(
  () => [form.price_from, form.price_to, form.include_no_price],
  () => {
    if (priceDebounceTimer) clearTimeout(priceDebounceTimer)
    priceDebounceTimer = setTimeout(() => submit(), 400)
  },
)

watch(
  () => [form.price_from, form.price_to],
  () => {
    if (!hasPriceBounds.value) {
      form.include_no_price = false
    }
  },
)

function onSearch(e) {
  e.preventDefault()
  if (qDebounceTimer) clearTimeout(qDebounceTimer)
  if (cityDebounceTimer) clearTimeout(cityDebounceTimer)
  if (providerDebounceTimer) clearTimeout(providerDebounceTimer)
  if (priceDebounceTimer) clearTimeout(priceDebounceTimer)
  submit()
}

function resetFilters() {
  form.q = ''
  form.type = ''
  form.category_id = ''
  form.city = ''
  form.provider = ''
  form.price_from = ''
  form.price_to = ''
  form.include_no_price = false
  form.sort = 'newest'

  submit()
}

function goFirstPage() {
  // Re-submit current filters without `page` param (so we land on page 1).
  submit()
}
</script>

<template>
  <Head title="Каталог" />

  <div class="py-8">
    <div class="mx-auto max-w-6xl px-4">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        <div>
          <h1 class="text-2xl font-semibold">Каталог</h1>
          <div class="mt-1 text-sm text-gray-500">
            <span class="font-medium text-gray-700">{{ offers.total ?? offers.data?.length ?? 0 }}</span> результатів
            <template v-if="offers.from && offers.to">
              <span class="text-gray-400">·</span> Показано {{ offers.from }}–{{ offers.to }}
            </template>
          </div>
        </div>
        <Link href="/" class="text-sm text-gray-600 hover:text-gray-900">На головну</Link>
      </div>

      <form class="mt-6 flex flex-wrap gap-3 items-end" @submit="onSearch">
        <div class="w-full">
          <div class="flex flex-wrap items-center gap-2">
            <div v-if="activeChips.length" class="text-xs text-gray-500 mr-2">Активні фільтри:</div>
            <button
              type="button"
              :disabled="!hasActiveFilters"
              class="inline-flex items-center rounded-full bg-gray-900 px-3 py-1 text-xs text-white hover:bg-black disabled:opacity-50 disabled:hover:bg-gray-900"
              @click="resetFilters"
              :title="hasActiveFilters ? 'Очистити всі фільтри' : 'Немає активних фільтрів'"
            >
              Очистити всі фільтри
            </button>

            <Link
              v-if="providerSlug"
              :href="`/providers/${providerSlug}`"
              class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs text-blue-700 hover:bg-blue-100"
              title="Повернутись на сторінку провайдера"
            >
              ← До провайдера
            </Link>
            <span
              v-for="chip in activeChips"
              :key="chip.key"
              class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-3 py-1 text-xs text-gray-700"
              :title="chip.title || chip.label"
            >
              <span class="whitespace-nowrap">{{ chip.label }}</span>
              <button
                type="button"
                class="rounded-full text-gray-400 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2"
                :aria-label="'Прибрати фільтр: ' + (chip.title || chip.label)"
                @click="clearChip(chip.key)"
              >
                ×
              </button>
            </span>
          </div>
        </div>
        <div class="w-full max-w-md">
          <label for="catalog-q" class="text-xs text-gray-500">Пошук</label>
          <input
            id="catalog-q"
            v-model="form.q"
            type="search"
            autocomplete="off"
            class="mt-1 w-full rounded-md border-gray-300"
            placeholder="напр. електрик, ремонт, булочна"
            @keydown.enter.prevent="onSearch"
          />
        </div>

        <div>
          <label for="catalog-type" class="text-xs text-gray-500">Тип</label>
          <select id="catalog-type" v-model="form.type" class="mt-1 rounded-md border-gray-300" @change="submit">
            <option value="">Усі</option>
            <option value="service">Послуги</option>
            <option value="product">Товари</option>
          </select>
        </div>

        <div>
          <label for="catalog-category" class="text-xs text-gray-500">Категорія</label>
          <select id="catalog-category" v-model="form.category_id" class="mt-1 rounded-md border-gray-300" @change="submit">
            <option value="">Усі</option>
            <option
              v-for="c in flatCategories"
              :key="c.id"
              :value="String(c.id)"
            >
              {{ c.label }}
            </option>
          </select>
        </div>

        <div>
          <label for="catalog-city" class="text-xs text-gray-500">Місто</label>
          <input
            id="catalog-city"
            v-model="form.city"
            type="search"
            autocomplete="address-level2"
            class="mt-1 w-48 rounded-md border-gray-300"
            placeholder="напр. Київ"
            @keydown.enter.prevent="onSearch"
          />
        </div>

        <div>
          <label for="catalog-provider" class="text-xs text-gray-500">Провайдер (slug)</label>
          <input
            id="catalog-provider"
            v-model="form.provider"
            type="search"
            autocomplete="off"
            class="mt-1 w-48 rounded-md border-gray-300"
            placeholder="напр. demo-provider"
            :title="'Slug береться з URL профілю провайдера: /providers/{slug}'"
            @keydown.enter.prevent="onSearch"
          />
          <div class="mt-1 text-[11px] text-gray-400">Підказка: slug = частина URL профілю провайдера (<span class="font-mono">/providers/{slug}</span>).</div>
        </div>

        <div>
          <fieldset>
            <legend class="text-xs text-gray-500">Ціна</legend>
            <div class="mt-1 flex items-center gap-2">
              <label for="catalog-price-from" class="sr-only">Ціна від</label>
              <input
                id="catalog-price-from"
                v-model="form.price_from"
                type="number"
                min="0"
                step="1"
                inputmode="numeric"
                autocomplete="off"
                class="w-28 rounded-md border-gray-300"
                placeholder="від"
                @keydown.enter.prevent="onSearch"
              />
              <span class="text-xs text-gray-400">—</span>
              <label for="catalog-price-to" class="sr-only">Ціна до</label>
              <input
                id="catalog-price-to"
                v-model="form.price_to"
                type="number"
                min="0"
                step="1"
                inputmode="numeric"
                autocomplete="off"
                class="w-28 rounded-md border-gray-300"
                placeholder="до"
                @keydown.enter.prevent="onSearch"
              />
            </div>
            <div class="mt-1 text-[11px] text-gray-400">
              Можна заповнити тільки одне поле («від» або «до») або обидва.
            </div>
            <label
              class="mt-2 inline-flex items-center gap-2 text-xs text-gray-600"
              :title="!hasPriceBounds ? 'Спочатку задайте межі ціни (від/до), щоб увімкнути цей фільтр.' : ''"
            >
              <input
                id="catalog-include-no-price"
                type="checkbox"
                v-model="form.include_no_price"
                class="rounded border-gray-300"
                :disabled="!hasPriceBounds"
              />
              <span :class="!hasPriceBounds ? 'text-gray-400' : ''">Включати «ціна за домовленістю»</span>
            </label>
            <div v-if="!hasPriceBounds" class="mt-1 text-xs text-gray-400">
              Підказка: цей фільтр активується після введення «від» або «до».
            </div>
          </fieldset>
        </div>

        <div>
          <label for="catalog-sort" class="text-xs text-gray-500">Сортування</label>
          <select id="catalog-sort" v-model="form.sort" class="mt-1 rounded-md border-gray-300" @change="submit">
            <option value="newest">Найновіші</option>
            <option value="price_asc">Ціна: зростання</option>
            <option value="price_desc">Ціна: спадання</option>
          </select>
        </div>

        <button class="rounded-md bg-black px-4 py-2 text-white">Шукати</button>
        <button
          type="button"
          class="rounded-md border border-gray-300 px-4 py-2 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="!hasActiveFilters"
          :title="!hasActiveFilters ? 'Немає активних фільтрів для скидання.' : ''"
          @click="resetFilters"
        >
          Скинути
        </button>
      </form>

      <div v-if="offers.data?.length" class="mt-8 grid grid-cols-1 gap-4 md:grid-cols-2">
        <div
          v-for="offer in offers.data"
          :key="offer.id"
          class="rounded-lg border border-gray-200 bg-white p-4"
        >
          <div class="flex items-start justify-between gap-4">
            <div>
              <div class="text-sm text-gray-500">
                {{ offerTypeLabel(offer.type) }}
                <span v-if="offer.category">· {{ offer.category.name }}</span>
              </div>
              <div class="mt-0.5 text-lg font-semibold leading-snug">{{ offer.title }}</div>

              <div class="mt-1 text-sm text-gray-600">
                <Link
                  v-if="offer.business_profile?.slug"
                  :href="`/providers/${offer.business_profile.slug}`"
                  class="font-medium text-blue-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 rounded"
                >
                  {{ offer.business_profile?.name }}
                </Link>
                <span v-else>
                  {{ offer.business_profile?.name }}
                </span>
                <span v-if="offer.business_profile?.city">· {{ offer.business_profile.city }}</span>
              </div>
            </div>

            <div class="flex flex-col items-end gap-2">
              <div class="text-sm font-medium text-gray-800 whitespace-nowrap">{{ formatPrice(offer) }}</div>

            </div>
          </div>

          <div v-if="offer.description" class="mt-3 text-sm text-gray-700 line-clamp-3">
            {{ offer.description }}
          </div>
        </div>
      </div>

      <EmptyStateCard
        v-else
        class="mt-8"
        title="Нічого не знайдено"
        description="Спробуйте змінити або очистити фільтри."
      >
        <div class="flex flex-wrap gap-2">
          <button
            v-if="activeChips.length"
            type="button"
            class="inline-flex items-center rounded-md bg-gray-900 px-3 py-2 text-sm text-white hover:bg-black"
            @click="resetFilters"
          >
            Очистити всі фільтри
          </button>

          <button
            v-if="(offers.current_page ?? 1) > 1"
            type="button"
            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 hover:bg-gray-50"
            @click="goFirstPage"
          >
            На першу сторінку
          </button>
        </div>
      </EmptyStateCard>

      <div v-if="(offers.last_page ?? 1) > 1" class="mt-8 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2">
          <Link
            v-if="offers.prev_page_url"
            :href="offers.prev_page_url"
            class="text-sm text-blue-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 rounded"
          >
            Назад
          </Link>
          <span v-else class="text-sm text-gray-300">Назад</span>
        </div>

        <nav aria-label="Пагінація" class="flex flex-wrap items-center justify-center gap-1">
          <template v-if="offers.links?.length">
            <template v-for="(l, idx) in offers.links" :key="idx">
              <span
                v-if="l.active"
                class="px-2 py-1 text-sm rounded bg-gray-900 text-white"
                aria-current="page"
              >
                {{ l.label }}
              </span>

              <Link
                v-else-if="l.url"
                :href="l.url"
                class="px-2 py-1 text-sm rounded text-gray-700 hover:bg-gray-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                :aria-label="'Перейти на сторінку: ' + l.label"
              >
                <span v-html="l.label" />
              </Link>

              <span v-else class="px-2 py-1 text-sm rounded text-gray-300">
                {{ l.label }}
              </span>
            </template>
          </template>
          <template v-else>
            <div class="text-sm text-gray-500">Сторінка {{ offers.current_page }} з {{ offers.last_page }}</div>
          </template>
        </nav>

        <div class="flex items-center gap-2">
          <Link
            v-if="offers.next_page_url"
            :href="offers.next_page_url"
            class="text-sm text-blue-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 rounded"
          >
            Далі
          </Link>
          <span v-else class="text-sm text-gray-300">Далі</span>
        </div>
      </div>
    </div>
  </div>
</template>
