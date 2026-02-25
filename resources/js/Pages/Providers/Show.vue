<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { offerTypeLabel, formatPrice, normalizeWebsite, formatAvgRatingUk } from '@/lib/formatters'

const props = defineProps({
  provider: Object,
  eligibleDealId: Number,
})

const ratingText = () => formatAvgRatingUk(props.provider?.reviews_avg_rating)
const normalizedWebsiteHref = () => normalizeWebsite(props.provider?.website)

const portfolioLimit = 6
const showAllPortfolio = ref(false)
const portfolioPosts = computed(() => props.provider?.portfolio_posts || [])
const hasMorePortfolio = computed(() => portfolioPosts.value.length > portfolioLimit)
const portfolioPostsToShow = computed(() => (showAllPortfolio.value ? portfolioPosts.value : portfolioPosts.value.slice(0, portfolioLimit)))
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
            <div v-if="provider.offers_count !== undefined">Пропозицій: <span class="font-medium text-gray-800">{{ provider.offers_count }}</span></div>
            <div v-if="provider.reviews_count !== undefined">Відгуків: <span class="font-medium text-gray-800">{{ provider.reviews_count }}</span></div>
            <div v-if="ratingText()">Рейтинг: <span class="font-medium text-gray-800">{{ ratingText() }}/5</span></div>
          </div>
        </div>
        <div class="flex gap-3">
          <Link href="/catalog" class="text-sm text-blue-600 hover:underline">Каталог</Link>
        </div>
      </div>

      <!-- Header-like block (Instagram-ish direction for MVP) -->
      <div class="mt-6 rounded-lg border border-gray-200 bg-white p-4">
        <div class="text-sm text-gray-700" v-if="provider.about">{{ provider.about }}</div>
        <div class="mt-3 flex flex-wrap gap-4 text-sm text-gray-600">
          <div v-if="provider.phone">☎ {{ provider.phone }}</div>
          <div v-if="provider.website">
            <a class="text-blue-600 hover:underline" :href="normalizedWebsiteHref()" target="_blank" rel="noopener noreferrer">
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
          >
            <img
              :src="'/' + story.media_path"
              alt=""
              class="h-full w-full object-cover"
              loading="lazy"
            />
          </a>
        </div>

        <div v-else class="mt-3 rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-600">
          Поки що немає історій
        </div>
      </div>

      <!-- Latest portfolio -->
      <div class="mt-8">
        <div class="flex items-center justify-between gap-4">
          <h2 class="text-lg font-semibold">Останні роботи</h2>
          <button
            v-if="hasMorePortfolio"
            type="button"
            class="text-sm text-blue-600 hover:underline"
            @click="showAllPortfolio = !showAllPortfolio"
          >
            {{ showAllPortfolio ? 'Згорнути' : `Дивитися всі (${portfolioPosts.length})` }}
          </button>
        </div>

        <div v-if="portfolioPostsToShow.length" class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-3">
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

        <div v-else class="mt-3 rounded-lg border border-gray-200 bg-white p-6 text-sm text-gray-700">
          <div class="font-medium">Поки що немає робіт</div>
          <div class="mt-1 text-gray-600">Коли провайдер опублікує портфоліо — воно з'явиться тут.</div>
        </div>

        <div v-if="hasMorePortfolio && !showAllPortfolio" class="mt-3 text-sm text-gray-500">
          Показано {{ portfolioLimit }} з {{ portfolioPosts.length }}
        </div>
      </div>

      <!-- Offers -->
      <div class="mt-8">
        <h2 class="text-lg font-semibold">Пропозиції</h2>

        <div v-if="provider.offers?.length" class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
          <div
            v-for="offer in provider.offers"
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

        <div v-else class="mt-3 rounded-lg border border-gray-200 bg-white p-6 text-sm text-gray-700">
          <div class="font-medium">Поки що немає пропозицій</div>
          <div class="mt-1 text-gray-600">Коли провайдер додасть оголошення — воно з'явиться тут.</div>
        </div>
      </div>

      <!-- Reviews -->
      <div class="mt-8">
        <div class="flex items-center justify-between gap-4">
          <h2 class="text-lg font-semibold">Відгуки</h2>
          <Link
            v-if="eligibleDealId"
            :href="route('reviews.create', eligibleDealId)"
            class="text-sm text-blue-600 hover:underline"
          >
            Залишити відгук
          </Link>
        </div>
        <div v-if="provider.reviews?.length" class="mt-3 space-y-3">
          <div
            v-for="review in provider.reviews"
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

        <div v-else class="mt-3 rounded-lg border border-gray-200 bg-white p-6 text-sm text-gray-700">
          <div class="font-medium">Поки що немає відгуків</div>
          <div class="mt-1 text-gray-600">
            Відгуки з’являються після завершення угоди.
            <span v-if="eligibleDealId">Можете залишити свій відгук вище.</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
