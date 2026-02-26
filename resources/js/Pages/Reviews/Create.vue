<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Head, useForm, Link } from '@inertiajs/vue3'

const props = defineProps({
  deal: Object,
  businessProfile: Object,
})

const form = useForm({
  rating: 5,
  body: '',
})

const submit = () => {
  form.post(route('reviews.store', props.deal.id))
}
</script>

<template>
  <Head title="Залишити відгук" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Відгук — {{ businessProfile.name }}
        </h2>
        <div class="flex gap-3">
          <Link :href="route('providers.show', businessProfile.slug)" class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">
            До сторінки провайдера
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow sm:rounded-lg">
          <div class="mb-6 text-sm text-gray-600">
            <div v-if="deal.offer" class="font-medium">Офер: {{ deal.offer.title }}</div>
            <div>Статус угоди: <span class="font-medium">{{ deal.status }}</span></div>
          </div>

          <form @submit.prevent="submit" class="space-y-6">
            <div>
              <InputLabel for="rating" value="Оцінка" />
              <select id="rating" v-model.number="form.rating" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option :value="5">5 — відмінно</option>
                <option :value="4">4 — добре</option>
                <option :value="3">3 — нормально</option>
                <option :value="2">2 — погано</option>
                <option :value="1">1 — дуже погано</option>
              </select>
              <InputError class="mt-2" :message="form.errors.rating" />
            </div>

            <div>
              <InputLabel for="body" value="Коментар (необов'язково)" />
              <textarea
                id="body"
                v-model="form.body"
                rows="5"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                placeholder="Напишіть кілька слів про досвід..."
              />
              <InputError class="mt-2" :message="form.errors.body" />
            </div>

            <div class="flex items-center gap-4">
              <PrimaryButton :disabled="form.processing">Надіслати відгук</PrimaryButton>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
