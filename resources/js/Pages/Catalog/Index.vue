<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { reactive, watch } from 'vue'

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
  sort: props.filters?.sort || 'newest',
})

function submit() {
  router.get(
    '/catalog',
    {
      q: (form.q || '').trim() || undefined,
      type: form.type || undefined,
      category_id: form.category_id || undefined,
      city: (form.city || '').trim() || undefined,
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

let qDebounceTimer = null

watch(
  () => form.q,
  () => {
    if (qDebounceTimer) clearTimeout(qDebounceTimer)
    qDebounceTimer = setTimeout(() => submit(), 400)
  },
)

function onSearch(e) {
  e.preventDefault()
  if (qDebounceTimer) clearTimeout(qDebounceTimer)
  submit()
}

function resetFilters() {
  form.q = ''
  form.type = ''
  form.category_id = ''
  form.city = ''
  form.sort = 'newest'

  submit()
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

      <form class="mt-6 flex flex-wrap gap-3 items-end" @submit="onSearch">
        <div class="w-full max-w-md">
          <div class="text-xs text-gray-500">Пошук</div>
          <input
            v-model="form.q"
            class="mt-1 w-full rounded-md border-gray-300"
            placeholder="напр. електрик, ремонт, булочна"
            @keydown.enter.prevent="onSearch"
          />
        </div>

        <div>
          <div class="text-xs text-gray-500">Тип</div>
          <select v-model="form.type" class="mt-1 rounded-md border-gray-300" @change="submit">
            <option value="">Усі</option>
            <option value="service">Послуги</option>
            <option value="product">Товари</option>
          </select>
        </div>

        <div>
          <div class="text-xs text-gray-500">Категорія</div>
          <select v-model="form.category_id" class="mt-1 rounded-md border-gray-300" @change="submit">
            <option value="">Усі</option>
            <option v-for="c in categories" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
          </select>
        </div>

        <div>
          <div class="text-xs text-gray-500">Місто</div>
          <input
            v-model="form.city"
            class="mt-1 w-48 rounded-md border-gray-300"
            placeholder="напр. Київ"
            @keydown.enter.prevent="submit"
          />
        </div>

        <div>
          <div class="text-xs text-gray-500">Сортування</div>
          <select v-model="form.sort" class="mt-1 rounded-md border-gray-300" @change="submit">
            <option value="newest">Найновіші</option>
            <option value="price_asc">Ціна: зростання</option>
            <option value="price_desc">Ціна: спадання</option>
          </select>
        </div>

        <button class="rounded-md bg-black px-4 py-2 text-white">Шукати</button>
        <button type="button" class="rounded-md border border-gray-300 px-4 py-2 text-sm" @click="resetFilters">Скинути</button>
      </form>

      <div v-if="offers.data?.length" class="mt-8 grid grid-cols-1 gap-4 md:grid-cols-2">
        <div
          v-for="offer in offers.data"
          :key="offer.id"
          class="rounded-lg border border-gray-200 bg-white p-4"
        >
          <div class="flex items-start justify-between gap-3">
            <div>
              <div class="text-sm text-gray-500">
                {{ offer.type }}
                <span v-if="offer.category">· {{ offer.category.name }}</span>
              </div>
              <div class="text-lg font-semibold">{{ offer.title }}</div>
              <div class="mt-1 text-sm text-gray-600">
                {{ offer.business_profile?.name }}
                <span v-if="offer.business_profile?.city">· {{ offer.business_profile.city }}</span>
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

      <div v-else class="mt-8 rounded-lg border border-gray-200 bg-white p-6 text-sm text-gray-700">
        Нічого не знайдено. Спробуйте змінити фільтри.
      </div>

      <div class="mt-8 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2">
          <Link
            v-if="offers.prev_page_url"
            :href="offers.prev_page_url"
            class="text-sm text-blue-600 hover:underline"
          >
            Назад
          </Link>
          <span v-else class="text-sm text-gray-300">Назад</span>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-1">
          <template v-if="offers.links?.length">
            <span
              v-for="(l, idx) in offers.links"
              :key="idx"
              class="px-2 py-1 text-sm rounded"
              :class="l.active ? 'bg-gray-900 text-white' : 'text-gray-700'"
            >
              <Link
                v-if="l.url"
                :href="l.url"
                class="hover:underline"
                preserve-scroll
                preserve-state
              >
                <span v-html="l.label" />
              </Link>
              <span v-else class="text-gray-300" v-html="l.label" />
            </span>
          </template>
          <template v-else>
            <div class="text-sm text-gray-500">Сторінка {{ offers.current_page }} з {{ offers.last_page }}</div>
          </template>
        </div>

        <div class="flex items-center gap-2">
          <Link
            v-if="offers.next_page_url"
            :href="offers.next_page_url"
            class="text-sm text-blue-600 hover:underline"
          >
            Далі
          </Link>
          <span v-else class="text-sm text-gray-300">Далі</span>
        </div>
      </div>
    </div>
  </div>
</template>
