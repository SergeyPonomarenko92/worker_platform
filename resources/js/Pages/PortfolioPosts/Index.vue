<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    businessProfile: Object,
    posts: Array,
});

const formatDate = (value) => {
    if (!value) return '—';
    return new Date(value).toLocaleString();
};
</script>

<template>
    <Head title="Портфоліо" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Портфоліо — {{ businessProfile.name }}
                </h2>
                <div class="flex gap-3">
                    <Link :href="route('dashboard.business-profiles.index')" class="text-sm text-indigo-600 hover:underline">Профілі бізнесу</Link>
                    <Link :href="route('dashboard.business-profiles.edit', businessProfile.id)" class="text-sm text-indigo-600 hover:underline">Профіль</Link>
                    <Link :href="route('dashboard.portfolio-posts.create', businessProfile.id)">
                        <PrimaryButton>Створити пост</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <div v-if="posts.length === 0" class="text-sm text-gray-700">Поки що немає постів у портфоліо.</div>

                    <ul v-else class="divide-y divide-gray-200">
                        <li v-for="post in posts" :key="post.id" class="py-3 flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900">{{ post.title }}</div>
                                <div class="text-sm text-gray-600">
                                    Опубліковано: {{ formatDate(post.published_at) }}
                                </div>
                            </div>
                            <Link :href="route('dashboard.portfolio-posts.edit', [businessProfile.id, post.id])" class="text-sm text-indigo-600 hover:underline">Редагувати</Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
