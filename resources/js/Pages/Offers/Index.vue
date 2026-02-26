<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import EmptyStateCard from '@/Components/EmptyStateCard.vue'
import { Head, Link } from '@inertiajs/vue3'
import { offerTypeLabel, formatPrice } from '@/lib/formatters'

const props = defineProps({
    businessProfile: Object,
    offers: Array,
})
</script>

<template>
    <Head title="Пропозиції" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Пропозиції — {{ businessProfile.name }}
                </h2>
                <div class="flex gap-3 flex-wrap justify-end">
                    <Link :href="route('dashboard.business-profiles.index')" class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">Профілі бізнесу</Link>
                    <Link :href="route('dashboard.business-profiles.edit', businessProfile.id)" class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">Профіль</Link>
                    <Link :href="route('dashboard.portfolio-posts.index', businessProfile.id)" class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">Портфоліо</Link>
                    <Link :href="route('dashboard.stories.index', businessProfile.id)" class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">Історії</Link>
                    <Link :href="route('dashboard.deals.index', businessProfile.id)" class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">Угоди</Link>
                    <Link :href="route('dashboard.offers.create', businessProfile.id)">
                        <PrimaryButton>Створити пропозицію</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <EmptyStateCard
                        v-if="offers.length === 0"
                        title="Поки що немає пропозицій"
                        description="Додайте першу пропозицію — вона з’явиться у каталозі та на публічній сторінці провайдера."
                        announce
                    >
                        <div class="flex flex-wrap items-center gap-3">
                            <Link :href="route('dashboard.offers.create', businessProfile.id)">
                                <PrimaryButton>Створити пропозицію</PrimaryButton>
                            </Link>
                            <Link
                                v-if="businessProfile.slug"
                                :href="route('providers.show', businessProfile.slug)"
                                class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded"
                            >
                                Переглянути публічну сторінку
                            </Link>
                        </div>
                    </EmptyStateCard>

                    <ul v-else class="divide-y divide-gray-200">
                        <li v-for="offer in offers" :key="offer.id" class="py-3 flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900">{{ offer.title }}</div>
                                <div class="text-sm text-gray-600">
                                    {{ offerTypeLabel(offer.type) }}
                                    <span v-if="offer.category">· {{ offer.category.name }}</span>
                                    <span class="text-gray-400">·</span>
                                    <span class="font-medium text-gray-800">{{ formatPrice(offer) }}</span>
                                </div>
                            </div>
                            <Link :href="route('dashboard.offers.edit', [businessProfile.id, offer.id])" class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">Редагувати</Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
