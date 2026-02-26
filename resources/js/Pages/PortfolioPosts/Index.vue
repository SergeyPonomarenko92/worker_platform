<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import EmptyStateCard from '@/Components/EmptyStateCard.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
    businessProfile: Object,
    posts: Array,
    now: String,
})

const formatDate = (value) => {
    if (!value) return '—'
    return new Date(value).toLocaleString('uk-UA')
}

const statusFor = (post) => {
    if (!post.published_at) {
        return { label: 'Чернетка', cls: 'bg-gray-100 text-gray-700' }
    }

    const publishedAt = new Date(post.published_at)
    const now = new Date(props.now)

    if (publishedAt.getTime() > now.getTime()) {
        return { label: 'Заплановано', cls: 'bg-yellow-100 text-yellow-800' }
    }

    return { label: 'Опубліковано', cls: 'bg-green-100 text-green-800' }
}
</script>

<template>
    <Head title="Портфоліо" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Портфоліо — {{ businessProfile.name }}
                </h2>
                <div class="flex gap-3 flex-wrap justify-end">
                    <Link :href="route('dashboard.business-profiles.index')" class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">Профілі бізнесу</Link>
                    <Link :href="route('dashboard.business-profiles.edit', businessProfile.id)" class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">Профіль</Link>
                    <Link :href="route('dashboard.offers.index', businessProfile.id)" class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">Пропозиції</Link>
                    <Link :href="route('dashboard.stories.index', businessProfile.id)" class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">Історії</Link>
                    <Link :href="route('dashboard.deals.index', businessProfile.id)" class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">Угоди</Link>
                    <Link :href="route('dashboard.portfolio-posts.create', businessProfile.id)">
                        <PrimaryButton>Створити пост</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <EmptyStateCard
                        v-if="posts.length === 0"
                        title="Поки що немає робіт у портфоліо"
                        description="Додайте перший пост — він з’явиться на публічній сторінці провайдера."
                    >
                        <div class="flex flex-wrap items-center gap-3">
                            <Link :href="route('dashboard.portfolio-posts.create', businessProfile.id)">
                                <PrimaryButton>Створити пост</PrimaryButton>
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
                        <li v-for="post in posts" :key="post.id" class="py-3 flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <div class="font-medium text-gray-900">{{ post.title }}</div>
                                    <span class="text-xs px-2 py-0.5 rounded" :class="statusFor(post).cls">
                                        {{ statusFor(post).label }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600">Опубліковано: {{ formatDate(post.published_at) }}</div>
                            </div>
                            <Link
                                :href="route('dashboard.portfolio-posts.edit', [businessProfile.id, post.id])"
                                class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded"
                            >
                                Редагувати
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
