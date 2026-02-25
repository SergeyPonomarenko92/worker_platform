<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import EmptyStateCard from '@/Components/EmptyStateCard.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    businessProfile: Object,
    stories: Array,
    now: String,
    showExpired: Boolean,
})

const form = useForm({})

const formatDate = (value) => {
    if (!value) return '—'
    return new Date(value).toLocaleString('uk-UA')
}

const statusFor = (story) => {
    const expiresAt = new Date(story.expires_at)
    const now = new Date(props.now)

    if (expiresAt.getTime() <= now.getTime()) {
        return { label: 'Протерміновано', cls: 'bg-gray-100 text-gray-700' }
    }

    return { label: 'Активна', cls: 'bg-green-100 text-green-800' }
}

const destroy = (storyId) => {
    if (!confirm('Видалити історію?')) return

    form.delete(route('dashboard.stories.destroy', [props.businessProfile.id, storyId]))
}
</script>

<template>
    <Head title="Історії" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Історії — {{ businessProfile.name }}
                </h2>
                <div class="flex gap-3 flex-wrap justify-end">
                    <Link :href="route('dashboard.business-profiles.index')" class="text-sm text-indigo-600 hover:underline">Профілі бізнесу</Link>
                    <Link :href="route('dashboard.business-profiles.edit', businessProfile.id)" class="text-sm text-indigo-600 hover:underline">Профіль</Link>
                    <Link :href="route('dashboard.offers.index', businessProfile.id)" class="text-sm text-indigo-600 hover:underline">Пропозиції</Link>
                    <Link :href="route('dashboard.portfolio-posts.index', businessProfile.id)" class="text-sm text-indigo-600 hover:underline">Портфоліо</Link>
                    <Link :href="route('dashboard.deals.index', businessProfile.id)" class="text-sm text-indigo-600 hover:underline">Угоди</Link>
                    <Link :href="route('dashboard.stories.create', businessProfile.id)">
                        <PrimaryButton>Створити історію</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div class="text-sm text-gray-600">За замовчуванням показані лише активні історії.</div>
                        <Link
                            class="text-sm text-indigo-600 hover:underline"
                            :href="route('dashboard.stories.index', businessProfile.id) + (showExpired ? '' : '?show_expired=1')"
                        >
                            {{ showExpired ? 'Сховати протерміновані' : 'Показати протерміновані' }}
                        </Link>
                    </div>

                    <EmptyStateCard
                        v-if="stories.length === 0"
                        :title="showExpired ? 'Поки що немає історій' : 'Поки що немає активних історій'"
                        description="Створіть історію — вона з’явиться на публічній сторінці провайдера й буде активною до дати завершення."
                    >
                        <div class="flex flex-wrap items-center gap-3">
                            <Link :href="route('dashboard.stories.create', businessProfile.id)">
                                <PrimaryButton>Створити історію</PrimaryButton>
                            </Link>
                            <Link
                                v-if="businessProfile.slug"
                                :href="route('providers.show', businessProfile.slug)"
                                class="text-sm text-indigo-600 hover:underline"
                            >
                                Переглянути публічну сторінку
                            </Link>
                        </div>
                    </EmptyStateCard>

                    <ul v-else class="divide-y divide-gray-200">
                        <li v-for="story in stories" :key="story.id" class="py-3 flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <div class="font-medium text-gray-900">{{ story.caption || story.media_path }}</div>
                                    <span class="text-xs px-2 py-0.5 rounded" :class="statusFor(story).cls">
                                        {{ statusFor(story).label }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600">Діє до: {{ formatDate(story.expires_at) }}</div>
                            </div>
                            <div class="flex items-center gap-3">
                                <Link
                                    :href="route('dashboard.stories.edit', [businessProfile.id, story.id])"
                                    class="text-sm text-indigo-600 hover:underline"
                                >
                                    Редагувати
                                </Link>
                                <DangerButton type="button" @click="destroy(story.id)">Видалити</DangerButton>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
