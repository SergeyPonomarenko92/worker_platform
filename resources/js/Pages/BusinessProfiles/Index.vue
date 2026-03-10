<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import EmptyStateCard from '@/Components/EmptyStateCard.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
    profiles: Array,
})
</script>

<template>
    <Head title="Профілі бізнесу" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <Breadcrumbs :items="[
                        { label: 'Кабінет', href: route('dashboard') },
                        { label: 'Профілі бізнесу', current: true },
                    ]" />
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">Профілі бізнесу</h2>
                </div>
                <Link :href="route('dashboard.business-profiles.create')">
                    <PrimaryButton>Створити профіль</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <EmptyStateCard
                        v-if="profiles.length === 0"
                        title="Поки що немає профілів бізнесу"
                        description="Створіть перший профіль — він стане вашою публічною сторінкою та основою для пропозицій, портфоліо, історій і угод."
                        announce
                    >
                        <div class="flex flex-wrap gap-3">
                            <Link :href="route('dashboard.business-profiles.create')">
                                <PrimaryButton>Створити профіль</PrimaryButton>
                            </Link>
                        </div>
                    </EmptyStateCard>

                    <ul v-else class="divide-y divide-gray-200">
                        <li v-for="p in profiles" :key="p.id" class="py-3 flex items-center justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <div class="font-medium text-gray-900">{{ p.name }}</div>

                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="p.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'"
                                    >
                                        {{ p.is_active ? 'Активний' : 'Неактивний' }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600">/providers/{{ p.slug }}</div>
                            </div>
                            <div class="flex flex-wrap items-center justify-end gap-x-4 gap-y-1 text-sm">
                                <Link
                                    :href="route('providers.show', p.slug)"
                                    class="text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded"
                                    target="_blank"
                                    rel="noopener noreferrer nofollow"
                                    :title="`Відкрити публічну сторінку профілю: ${p.name}`"
                                    :aria-label="`Відкрити публічну сторінку профілю: ${p.name}`"
                                >
                                    Публічна сторінка
                                </Link>
                                <Link
                                    :href="route('dashboard.offers.index', p.id)"
                                    class="text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded"
                                    :title="`Перейти до пропозицій профілю: ${p.name}`"
                                    :aria-label="`Перейти до пропозицій профілю: ${p.name}`"
                                >
                                    Пропозиції
                                </Link>
                                <Link
                                    :href="route('dashboard.portfolio-posts.index', p.id)"
                                    class="text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded"
                                    :title="`Перейти до портфоліо профілю: ${p.name}`"
                                    :aria-label="`Перейти до портфоліо профілю: ${p.name}`"
                                >
                                    Портфоліо
                                </Link>
                                <Link
                                    :href="route('dashboard.stories.index', p.id)"
                                    class="text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded"
                                    :title="`Перейти до історій профілю: ${p.name}`"
                                    :aria-label="`Перейти до історій профілю: ${p.name}`"
                                >
                                    Історії
                                </Link>
                                <Link
                                    :href="route('dashboard.deals.index', p.id)"
                                    class="text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded"
                                    :title="`Перейти до угод профілю: ${p.name}`"
                                    :aria-label="`Перейти до угод профілю: ${p.name}`"
                                >
                                    Угоди
                                </Link>
                                <Link
                                    :href="route('dashboard.business-profiles.edit', p.id)"
                                    class="text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded"
                                    :title="`Редагувати профіль: ${p.name}`"
                                    :aria-label="`Редагувати профіль: ${p.name}`"
                                >
                                    Редагувати
                                </Link>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
