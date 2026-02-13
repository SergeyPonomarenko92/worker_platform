<script setup>
import { Head, Link } from '@inertiajs/vue3'

defineProps({
  provider: Object,
  eligibleDealId: Number,
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
            <a class="text-blue-600 hover:underline" :href="provider.website" target="_blank" rel="noreferrer">
              {{ provider.website }}
            </a>
          </div>
        </div>
      </div>

      <!-- Stories -->
      <div class="mt-8">
        <h2 class="text-lg font-semibold">Історії</h2>
        <div class="mt-3 flex gap-3 overflow-x-auto">
          <div
            v-for="story in provider.stories"
            :key="story.id"
            class="h-20 w-20 flex-shrink-0 rounded-full border border-gray-300 bg-gray-50"
            :title="story.caption || ''"
          />
          <div v-if="!provider.stories?.length" class="text-sm text-gray-500">Поки що немає історій</div>
        </div>
      </div>

      <!-- Latest portfolio -->
      <div class="mt-8">
        <h2 class="text-lg font-semibold">Останні роботи</h2>
        <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-3">
          <div
            v-for="post in provider.portfolio_posts"
            :key="post.id"
            class="rounded-lg border border-gray-200 bg-white p-4"
          >
            <div class="font-medium">{{ post.title }}</div>
            <div v-if="post.body" class="mt-2 text-sm text-gray-600 line-clamp-3">{{ post.body }}</div>
          </div>
          <div v-if="!provider.portfolio_posts?.length" class="text-sm text-gray-500">Поки що немає робіт</div>
        </div>
      </div>

      <!-- Offers -->
      <div class="mt-8">
        <h2 class="text-lg font-semibold">Пропозиції</h2>
        <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
          <div
            v-for="offer in provider.offers"
            :key="offer.id"
            class="rounded-lg border border-gray-200 bg-white p-4"
          >
            <div class="text-sm text-gray-500">{{ offer.type }}</div>
            <div class="text-lg font-semibold">{{ offer.title }}</div>
            <div v-if="offer.description" class="mt-2 text-sm text-gray-700">{{ offer.description }}</div>
          </div>
          <div v-if="!provider.offers?.length" class="text-sm text-gray-500">Поки що немає пропозицій</div>
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
        <div class="mt-3 space-y-3">
          <div
            v-for="review in provider.reviews"
            :key="review.id"
            class="rounded-lg border border-gray-200 bg-white p-4"
          >
            <div class="text-sm text-gray-500">Оцінка: {{ review.rating }}/5</div>
            <div v-if="review.body" class="mt-2 text-sm text-gray-700">{{ review.body }}</div>
          </div>
          <div v-if="!provider.reviews?.length" class="text-sm text-gray-500">Поки що немає відгуків</div>
        </div>
      </div>
    </div>
  </div>
</template>
