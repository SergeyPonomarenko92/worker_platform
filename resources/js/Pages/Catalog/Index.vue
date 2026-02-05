<script setup>
import { Head, Link, router } from '@inertiajs/vue3'

const props = defineProps({
  offers: Object,
  filters: Object,
})

function onSearch(e) {
  e.preventDefault()
  const form = e.target
  router.get('/catalog', {
    q: form.q.value || undefined,
    type: form.type.value || undefined,
  }, { preserveState: true })
}
</script>

<template>
  <Head title="Каталог" />

  <div class="py-8">
    <div class="mx-auto max-w-6xl px-4">
      <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">Каталог</h1>
        <Link href="/" class="text-sm text-gray-600 hover:text-gray-900">На головну</Link>
      </div>

      <form class="mt-6 flex flex-wrap gap-3" @submit="onSearch">
        <input
          name="q"
          class="w-full max-w-md rounded-md border-gray-300"
          placeholder="Пошук (напр. електрик, ремонт, булочна)"
          :defaultValue="filters?.q || ''"
        />

        <select name="type" class="rounded-md border-gray-300" :defaultValue="filters?.type || ''">
          <option value="">Усі</option>
          <option value="service">Послуги</option>
          <option value="product">Товари</option>
        </select>

        <button class="rounded-md bg-black px-4 py-2 text-white">Шукати</button>
      </form>

      <div class="mt-8 grid grid-cols-1 gap-4 md:grid-cols-2">
        <div
          v-for="offer in offers.data"
          :key="offer.id"
          class="rounded-lg border border-gray-200 bg-white p-4"
        >
          <div class="flex items-start justify-between gap-3">
            <div>
              <div class="text-sm text-gray-500">{{ offer.type }}</div>
              <div class="text-lg font-semibold">{{ offer.title }}</div>
              <div class="mt-1 text-sm text-gray-600">
                {{ offer.business_profile?.name }}
              </div>
            </div>
            <Link
              v-if="offer.business_profile?.slug"
              :href="`/providers/${offer.business_profile.slug}`"
              class="text-sm text-blue-600 hover:underline"
            >
              Профіль
            </Link>
          </div>

          <div v-if="offer.description" class="mt-3 text-sm text-gray-700">
            {{ offer.description }}
          </div>

          <div class="mt-3 text-sm text-gray-500">
            <span v-if="offer.price_from">від {{ offer.price_from }} {{ offer.currency }}</span>
            <span v-else>ціна за домовленістю</span>
          </div>
        </div>
      </div>

      <div class="mt-8 flex items-center justify-between">
        <Link v-if="offers.prev_page_url" :href="offers.prev_page_url" class="text-sm text-blue-600 hover:underline">
          Назад
        </Link>
        <div class="text-sm text-gray-500">Сторінка {{ offers.current_page }} з {{ offers.last_page }}</div>
        <Link v-if="offers.next_page_url" :href="offers.next_page_url" class="text-sm text-blue-600 hover:underline">
          Далі
        </Link>
      </div>
    </div>
  </div>
</template>
