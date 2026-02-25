<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import EmptyStateCard from '@/Components/EmptyStateCard.vue'
import { offerTypeLabel, formatPrice, normalizeWebsite, formatAvgRatingUk } from '@/lib/formatters'
import { providerShowUrl } from '@/lib/providerShowUrl'

const props = defineProps({
  provider: Object,
  eligibleDealId: Number,
  loadAllPortfolio: Boolean,
  loadAllReviews: Boolean,
  loadAllOffers: Boolean,
})

const ratingText = computed(() => formatAvgRatingUk(props.provider?.reviews_avg_rating))
const normalizedWebsiteHref = computed(() => normalizeWebsite(props.provider?.website))

const telHref = computed(() => {
  const raw = String(props.provider?.phone || '').trim()
  if (!raw) return null

  // Keep digits and an optional leading + (simple & robust for UA numbers).
  const cleaned = raw.replace(/(?!^)\+|[^\d+]/g, '')
  return cleaned ? `tel:${cleaned}` : null
})

const mapsHref = computed(() => {
  const parts = [props.provider?.country_code, props.provider?.city, props.provider?.address].filter(Boolean)
  if (!parts.length) return null

  const query = parts.join(', ')
  return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(query)}`
})

const providerPageAllOffersUrl = computed(() =>
  providerShowUrl(props.provider.slug, {
    all_offers: true,
    all_portfolio: props.loadAllPortfolio,
    all_reviews: props.loadAllReviews,
    hash: 'offers',
  })
)

const providerPageAllPortfolioUrl = computed(() =>
  providerShowUrl(props.provider.slug, {
    all_portfolio: true,
    all_offers: props.loadAllOffers,
    all_reviews: props.loadAllReviews,
    hash: 'portfolio',
  })
)

const providerPageAllReviewsUrl = computed(() =>
  providerShowUrl(props.provider.slug, {
    all_reviews: true,
    all_offers: props.loadAllOffers,
    all_portfolio: props.loadAllPortfolio,
    hash: 'reviews',
  })
)

const portfolioLimit = 6
const showAllPortfolio = ref(!!props.loadAllPortfolio)
const portfolioSectionRef = ref(null)

const portfolioPosts = computed(() => props.provider?.portfolio_posts || [])
const portfolioLoadedCount = computed(() => portfolioPosts.value.length)
const portfolioTotalCount = computed(() => props.provider?.published_portfolio_posts_count ?? portfolioLoadedCount.value)

// The backend currently preloads only the latest N items (see ProviderController). Keep UI honest about that.
// "Fully loaded" means we already received all published items in the current response.
const portfolioIsFullyLoaded = computed(() => portfolioLoadedCount.value >= portfolioTotalCount.value)
const hasMorePortfolio = computed(() => portfolioTotalCount.value > portfolioLimit)

// If backend already returned all published items, we can expand/collapse without a full page reload.
const portfolioCanToggleWithoutReload = computed(() => portfolioIsFullyLoaded.value)

const portfolioPostsToShow = computed(() => (showAllPortfolio.value ? portfolioPosts.value : portfolioPosts.value.slice(0, portfolioLimit)))

const togglePortfolio = () => {
  const nextValue = !showAllPortfolio.value
  showAllPortfolio.value = nextValue

  // When collapsing back to the limited view, keep user context.
  if (!nextValue) {
    portfolioSectionRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

const reviewsLimit = 5
const showAllReviews = ref(!!props.loadAllReviews)
const reviewsSectionRef = ref(null)
const reviews = computed(() => props.provider?.reviews || [])
const reviewsLoadedCount = computed(() => reviews.value.length)
const reviewsTotalCount = computed(() => props.provider?.reviews_count ?? reviewsLoadedCount.value)

// Like portfolio: backend preloads only the latest N items unless query param is enabled.
const reviewsIsFullyLoaded = computed(() => reviewsLoadedCount.value >= reviewsTotalCount.value)
const hasMoreReviews = computed(() => reviewsTotalCount.value > reviewsLimit)
const reviewsToShow = computed(() => (showAllReviews.value ? reviews.value : reviews.value.slice(0, reviewsLimit)))

const toggleReviews = () => {
  const nextValue = !showAllReviews.value
  showAllReviews.value = nextValue

  if (!nextValue) {
    reviewsSectionRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

const offersLimit = 6
const showAllOffers = ref(!!props.loadAllOffers)
const offersSectionRef = ref(null)
const offers = computed(() => props.provider?.offers || [])
const offersLoadedCount = computed(() => offers.value.length)
const offersTotalCount = computed(() => props.provider?.offers_count ?? offersLoadedCount.value)

// Same approach as portfolio/reviews: backend may preload only latest N offers.
const offersIsFullyLoaded = computed(() => offersLoadedCount.value >= offersTotalCount.value)
const hasMoreOffers = computed(() => offersTotalCount.value > offersLimit)
const offersToShow = computed(() => (showAllOffers.value ? offers.value : offers.value.slice(0, offersLimit)))

const toggleOffers = () => {
  const nextValue = !showAllOffers.value
  showAllOffers.value = nextValue

  if (!nextValue) {
    offersSectionRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

const scrollToSection = (sectionRef) => {
  sectionRef?.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

onMounted(() => {
  const hash = typeof window !== 'undefined' ? window.location.hash : ''
  if (!hash) return

  const target = decodeURIComponent(hash.replace(/^#/, ''))

  const map = {
    portfolio: portfolioSectionRef,
    offers: offersSectionRef,
    reviews: reviewsSectionRef,
  }

  const sectionRef = map[target]
  if (!sectionRef) return

  // Wait until DOM is painted (Inertia navigation may mount before layout settles).
  requestAnimationFrame(() => {
    sectionRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  })
})
</script>

<template>
  <Head :title="provider.name" />

  <div class="py-8">
    <div class="mx-auto max-w-6xl px-4">
      <div class="flex items-start justify-between gap-4">
        <div>
          <div class="text-sm text-gray-500">Провайдер</div>
          <h1 class="text-2xl font-semibold">{{ provider.name }}</h1>
          <div v-if="provider.city" class="mt-1 text-sm text-gray-600">{{ provider.city }}, {{ provider.country_code }}</div>

          <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-600">
            <button
              v-if="provider.offers_count !== undefined && provider.offers_count > 0"
              type="button"
              class="hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
              :aria-label="`Перейти до секції пропозицій. Пропозицій: ${provider.offers_count}`"
              @click="scrollToSection(offersSectionRef)"
            >
              Пропозицій: <span class="font-medium text-gray-800">{{ provider.offers_count }}</span>
            </button>
            <div v-else-if="provider.offers_count === 0" class="text-gray-600">
              Пропозицій: <span class="font-medium text-gray-800">0</span>
            </div>

            <button
              v-if="provider.reviews_count !== undefined && provider.reviews_count > 0"
              type="button"
              class="hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
              :aria-label="`Перейти до секції відгуків. Відгуків: ${provider.reviews_count}`"
              @click="scrollToSection(reviewsSectionRef)"
            >
              Відгуків: <span class="font-medium text-gray-800">{{ provider.reviews_count }}</span>
            </button>
            <div v-else-if="provider.reviews_count === 0" class="text-gray-600">
              Відгуків: <span class="font-medium text-gray-800">0</span>
            </div>

            <button
              v-if="portfolioTotalCount !== undefined && portfolioTotalCount !== null && portfolioTotalCount > 0"
              type="button"
              class="hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
              :aria-label="`Перейти до секції портфоліо. Робіт: ${portfolioTotalCount}`"
              @click="scrollToSection(portfolioSectionRef)"
            >
              Робіт: <span class="font-medium text-gray-800">{{ portfolioTotalCount }}</span>
            </button>
            <div v-else-if="portfolioTotalCount === 0" class="text-gray-600">
              Робіт: <span class="font-medium text-gray-800">0</span>
            </div>

            <div v-if="ratingText">Рейтинг: <span class="font-medium text-gray-800">{{ ratingText }}/5</span></div>
          </div>
        </div>
        <div class="flex gap-3">
          <Link href="/catalog" class="text-sm text-blue-600 hover:underline">Каталог</Link>
          <Link :href="`/catalog?provider=${provider.slug}`" class="text-sm text-blue-600 hover:underline">Пропозиції цього провайдера</Link>
        </div>
      </div>

      <!-- Header-like block (Instagram-ish direction for MVP) -->
      <div class="mt-6 rounded-lg border border-gray-200 bg-white p-4">
        <div class="text-sm text-gray-700" v-if="provider.about">{{ provider.about }}</div>
        <div class="mt-3 flex flex-wrap gap-4 text-sm text-gray-600">
          <div v-if="provider.phone">
            <a
              v-if="telHref"
              class="text-blue-600 hover:underline"
              :href="telHref"
              :aria-label="`Зателефонувати: ${provider.phone}`"
            >
              ☎ {{ provider.phone }}
            </a>
            <span v-else>☎ {{ provider.phone }}</span>
          </div>

          <div v-if="provider.address || provider.city">
            <a
              v-if="mapsHref"
              class="text-blue-600 hover:underline"
              :href="mapsHref"
              target="_blank"
              rel="noopener noreferrer"
              :aria-label="`Відкрити адресу на мапі: ${[provider.city, provider.address].filter(Boolean).join(', ')}`"
            >
              📍 {{ [provider.city, provider.address].filter(Boolean).join(', ') }}
            </a>
            <span v-else>📍 {{ [provider.city, provider.address].filter(Boolean).join(', ') }}</span>
          </div>

          <div v-if="provider.website">
            <a class="text-blue-600 hover:underline" :href="normalizedWebsiteHref" target="_blank" rel="noopener noreferrer">
              {{ provider.website }}
            </a>
          </div>
        </div>
      </div>

      <!-- Stories -->
      <div class="mt-8">
        <h2 class="text-lg font-semibold">Історії</h2>

        <div v-if="provider.stories?.length" class="mt-3 flex gap-3 overflow-x-auto">
          <a
            v-for="story in provider.stories"
            :key="story.id"
            :href="'/' + story.media_path"
            target="_blank"
            rel="noopener noreferrer"
            class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-full border border-gray-300 bg-gray-50"
            :title="story.caption || ''"
            :aria-label="story.caption ? `Історія: ${story.caption}` : 'Історія'"
          >
            <img
              :src="'/' + story.media_path"
              :alt="story.caption ? `Історія: ${story.caption}` : 'Історія'"
              class="h-full w-full object-cover"
              loading="lazy"
            />
          </a>
        </div>

        <div v-else class="mt-3">
          <EmptyStateCard
            title="Поки що немає історій"
            description="Історії з’являються, коли провайдер публікує короткі оновлення (фото/відео) з обмеженим терміном."
          />
        </div>
      </div>

      <!-- Latest portfolio -->
      <div id="portfolio" ref="portfolioSectionRef" class="mt-8">
        <div class="flex items-center justify-between gap-4">
          <h2 class="text-lg font-semibold">Останні роботи</h2>
          <Link
            v-if="hasMorePortfolio && !loadAllPortfolio && !portfolioIsFullyLoaded"
            class="text-sm text-blue-600 hover:underline"
            :href="providerPageAllPortfolioUrl"
            preserve-scroll
          >
            Дивитися всі ({{ portfolioTotalCount }})
          </Link>

          <button
            v-else-if="hasMorePortfolio"
            type="button"
            class="text-sm text-blue-600 hover:underline"
            :aria-expanded="showAllPortfolio"
            aria-controls="provider-portfolio-list"
            @click="togglePortfolio"
          >
            {{ showAllPortfolio ? 'Згорнути' : 'Дивитися всі (' + portfolioTotalCount + ')' }}
          </button>
        </div>

        <template v-if="portfolioPostsToShow.length">
          <div id="provider-portfolio-list" class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div
              v-for="post in portfolioPostsToShow"
              :key="post.id"
              class="rounded-lg border border-gray-200 bg-white p-4"
            >
              <div class="flex items-start justify-between gap-3">
                <div class="font-medium">{{ post.title }}</div>
                <div v-if="post.published_at" class="text-xs text-gray-400 whitespace-nowrap">
                  {{ new Date(post.published_at).toLocaleDateString('uk-UA') }}
                </div>
              </div>
              <div v-if="post.body" class="mt-2 text-sm text-gray-600 line-clamp-3">{{ post.body }}</div>
            </div>
          </div>

          <div v-if="hasMorePortfolio" class="mt-4 flex justify-center">
            <button
              v-if="!showAllPortfolio && portfolioCanToggleWithoutReload"
              type="button"
              class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
              :aria-expanded="showAllPortfolio"
              aria-controls="provider-portfolio-list"
              @click="togglePortfolio"
            >
              Показати всі роботи ({{ portfolioTotalCount }})
            </button>

            <Link
              v-else-if="!showAllPortfolio"
              class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
              :href="providerPageAllPortfolioUrl"
              preserve-scroll
            >
              Показати всі роботи ({{ portfolioTotalCount }})
            </Link>

            <Link
              v-else-if="!portfolioIsFullyLoaded && !loadAllPortfolio"
              class="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
              :href="providerPageAllPortfolioUrl"
              preserve-scroll
            >
              Завантажити всі роботи ({{ portfolioTotalCount }})
            </Link>

            <button
              v-else
              type="button"
              class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-900 focus-visible:ring-offset-2"
              :aria-expanded="showAllPortfolio"
              aria-controls="provider-portfolio-list"
              @click="togglePortfolio"
            >
              Згорнути роботи
            </button>
          </div>
        </template>

        <div v-else class="mt-3">
          <EmptyStateCard
            title="Поки що немає робіт"
            description="Коли провайдер опублікує портфоліо — воно з’явиться тут."
          />
        </div>

        <div v-if="hasMorePortfolio && !showAllPortfolio" class="mt-3 text-sm text-gray-500">
          Показано {{ portfolioLimit }} з {{ portfolioTotalCount }}
        </div>

        <div v-if="showAllPortfolio && !portfolioIsFullyLoaded" class="mt-3 text-sm text-gray-500">
          Показано останні {{ portfolioLoadedCount }} з {{ portfolioTotalCount }}.
        </div>
      </div>

      <!-- Offers -->
      <div id="offers" ref="offersSectionRef" class="mt-8">
        <div class="flex items-center justify-between gap-4">
          <h2 class="text-lg font-semibold">Пропозиції</h2>

          <Link
            v-if="hasMoreOffers && !loadAllOffers && !offersIsFullyLoaded"
            :href="providerPageAllOffersUrl"
            class="text-sm text-blue-600 hover:underline"
            preserve-scroll
          >
            Дивитися всі ({{ offersTotalCount }})
          </Link>

          <button
            v-else-if="hasMoreOffers"
            type="button"
            class="text-sm text-blue-600 hover:underline"
            :aria-expanded="showAllOffers"
            aria-controls="provider-offers-list"
            @click="toggleOffers"
          >
            {{ showAllOffers ? 'Згорнути' : `Дивитися всі (${offersTotalCount})` }}
          </button>
        </div>

        <template v-if="offersToShow.length">
          <div id="provider-offers-list" class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div
              v-for="offer in offersToShow"
              :key="offer.id"
              class="rounded-lg border border-gray-200 bg-white p-4"
            >
              <div class="flex items-start justify-between gap-3">
                <div>
                  <div class="text-sm text-gray-500">
                    {{ offerTypeLabel(offer.type) }}
                    <span v-if="offer.category">· {{ offer.category.name }}</span>
                  </div>
                  <div class="text-lg font-semibold">{{ offer.title }}</div>
                </div>
                <div class="text-sm text-gray-600 whitespace-nowrap">{{ formatPrice(offer) }}</div>
              </div>

              <div v-if="offer.description" class="mt-2 text-sm text-gray-700 line-clamp-3">{{ offer.description }}</div>
            </div>
          </div>

          <div
            v-if="hasMoreOffers && !showAllOffers && !loadAllOffers && !offersIsFullyLoaded"
            class="mt-4 flex justify-center"
          >
            <Link
              class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
              :href="providerPageAllOffersUrl"
              preserve-scroll
            >
              Показати всі пропозиції ({{ offersTotalCount }})
            </Link>
          </div>

          <div v-if="hasMoreOffers && !showAllOffers" class="mt-3 text-sm text-gray-500">Показано {{ offersLimit }} з {{ offersTotalCount }}</div>

          <div v-if="showAllOffers && !offersIsFullyLoaded" class="mt-3 text-sm text-gray-500">
            Показано останні {{ offersLoadedCount }} з {{ offersTotalCount }}.
          </div>
        </template>

        <div v-else class="mt-3">
          <EmptyStateCard
            title="Поки що немає пропозицій"
            description="Коли провайдер додасть оголошення — воно з’явиться тут."
          />
        </div>
      </div>

      <!-- Reviews -->
      <div id="reviews" ref="reviewsSectionRef" class="mt-8">
        <div class="flex items-center justify-between gap-4">
          <h2 class="text-lg font-semibold">Відгуки</h2>
          <div class="flex items-center gap-4">
            <Link
              v-if="hasMoreReviews && !loadAllReviews && !reviewsIsFullyLoaded"
              class="text-sm text-blue-600 hover:underline"
              :href="providerPageAllReviewsUrl"
              preserve-scroll
            >
              Дивитися всі ({{ reviewsTotalCount }})
            </Link>

            <button
              v-else-if="hasMoreReviews"
              type="button"
              class="text-sm text-blue-600 hover:underline"
              :aria-expanded="showAllReviews"
              aria-controls="provider-reviews-list"
              @click="toggleReviews"
            >
              {{ showAllReviews ? 'Згорнути' : `Дивитися всі (${reviewsTotalCount})` }}
            </button>

            <Link
              v-if="eligibleDealId"
              :href="route('reviews.create', eligibleDealId)"
              class="text-sm text-blue-600 hover:underline"
            >
              Залишити відгук
            </Link>
          </div>
        </div>

        <template v-if="reviewsToShow.length">
          <div id="provider-reviews-list" class="mt-3 space-y-3">
            <div
              v-for="review in reviewsToShow"
              :key="review.id"
              class="rounded-lg border border-gray-200 bg-white p-4"
            >
              <div class="flex items-center justify-between gap-3">
                <div class="text-sm text-gray-500">
                  <span v-if="review.client" class="font-medium text-gray-700">{{ review.client.name }}</span>
                  <span v-else>Клієнт</span>
                  — Оцінка: {{ review.rating }}/5
                </div>
                <div v-if="review.created_at" class="text-xs text-gray-400">
                  {{ new Date(review.created_at).toLocaleDateString('uk-UA') }}
                </div>
              </div>
              <div v-if="review.body" class="mt-2 text-sm text-gray-700">{{ review.body }}</div>
            </div>
          </div>

          <div
            v-if="hasMoreReviews && !showAllReviews && !loadAllReviews && !reviewsIsFullyLoaded"
            class="mt-4 flex justify-center"
          >
            <Link
              class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
              :href="providerPageAllReviewsUrl"
              preserve-scroll
            >
              Показати всі відгуки ({{ reviewsTotalCount }})
            </Link>
          </div>
        </template>

        <div v-else class="mt-3">
          <EmptyStateCard title="Поки що немає відгуків" description="Відгуки з’являються після завершення угоди.">
            <span v-if="eligibleDealId" class="text-gray-600">Можете залишити свій відгук вище.</span>
          </EmptyStateCard>
        </div>

        <div v-if="hasMoreReviews && !showAllReviews" class="mt-3 text-sm text-gray-500">
          Показано {{ reviewsLimit }} з {{ reviewsTotalCount }}
        </div>

        <div v-if="showAllReviews && !reviewsIsFullyLoaded" class="mt-3 text-sm text-gray-500">
          Показано останні {{ reviewsLoadedCount }} з {{ reviewsTotalCount }}.
        </div>
      </div>
    </div>
  </div>
</template>
