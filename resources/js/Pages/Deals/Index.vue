<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import EmptyStateCard from '@/Components/EmptyStateCard.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
    businessProfile: Object,
    deals: Array,
})

const statusLabel = (status) => {
    switch (status) {
        case 'draft':
            return 'Чернетка'
        case 'in_progress':
            return 'В процесі'
        case 'completed':
            return 'Завершено'
        case 'cancelled':
            return 'Скасовано'
        default:
            return status
    }
}
</script>

<template>
    <Head title="Угоди" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Угоди — {{ businessProfile.name }}
                </h2>
                <div class="flex gap-3 flex-wrap justify-end">
                    <Link :href="route('dashboard.business-profiles.index')" class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">Профілі бізнесу</Link>
                    <Link :href="route('dashboard.business-profiles.edit', businessProfile.id)" class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">Профіль</Link>
                    <Link :href="route('dashboard.offers.index', businessProfile.id)" class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">Пропозиції</Link>
                    <Link :href="route('dashboard.portfolio-posts.index', businessProfile.id)" class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">Портфоліо</Link>
                    <Link :href="route('dashboard.stories.index', businessProfile.id)" class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">Історії</Link>
                    <Link :href="route('dashboard.deals.create', businessProfile.id)">
                        <PrimaryButton>Створити угоду</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <EmptyStateCard
                        v-if="deals.length === 0"
                        title="Поки що немає угод"
                        description="Створіть першу угоду — після завершення клієнт зможе залишити відгук."
                        announce
                    >
                        <div class="flex flex-wrap gap-3">
                            <Link :href="route('dashboard.deals.create', businessProfile.id)">
                                <PrimaryButton>Створити угоду</PrimaryButton>
                            </Link>
                        </div>
                    </EmptyStateCard>

                    <ul v-else class="divide-y divide-gray-200">
                        <li v-for="deal in deals" :key="deal.id" class="py-3 flex items-start justify-between">
                            <div>
                                <div class="font-medium text-gray-900">Угода #{{ deal.id }}</div>
                                <div class="text-sm text-gray-600">
                                    Статус: {{ statusLabel(deal.status) }}
                                    <span v-if="deal.client"> · Клієнт: {{ deal.client.name }} ({{ deal.client.email }})</span>
                                    <span v-if="deal.offer"> · Офер: {{ deal.offer.title }}</span>
                                </div>
                            </div>
                            <Link :href="route('dashboard.deals.show', [businessProfile.id, deal.id])" class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">Відкрити</Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
