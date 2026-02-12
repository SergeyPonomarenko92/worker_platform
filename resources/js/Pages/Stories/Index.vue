<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    businessProfile: Object,
    stories: Array,
});

const form = useForm({});

const formatDate = (value) => {
    if (!value) return '—';
    return new Date(value).toLocaleString();
};

const destroy = (storyId) => {
    if (!confirm('Видалити історію?')) return;

    form.delete(route('dashboard.stories.destroy', [props.businessProfile.id, storyId]));
};
</script>

<template>
    <Head title="Історії" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Історії — {{ businessProfile.name }}
                </h2>
                <div class="flex gap-3">
                    <Link :href="route('dashboard.business-profiles.index')" class="text-sm text-indigo-600 hover:underline">Профілі бізнесу</Link>
                    <Link :href="route('dashboard.business-profiles.edit', businessProfile.id)" class="text-sm text-indigo-600 hover:underline">Профіль</Link>
                    <Link :href="route('dashboard.stories.create', businessProfile.id)">
                        <PrimaryButton>Створити історію</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <div v-if="stories.length === 0" class="text-sm text-gray-700">Поки що немає історій.</div>

                    <ul v-else class="divide-y divide-gray-200">
                        <li v-for="story in stories" :key="story.id" class="py-3 flex items-start justify-between">
                            <div>
                                <div class="font-medium text-gray-900">{{ story.caption || story.media_path }}</div>
                                <div class="text-sm text-gray-600">Діє до: {{ formatDate(story.expires_at) }}</div>
                            </div>
                            <div class="flex items-center gap-3">
                                <Link :href="route('dashboard.stories.edit', [businessProfile.id, story.id])" class="text-sm text-indigo-600 hover:underline">Редагувати</Link>
                                <DangerButton type="button" @click="destroy(story.id)">Видалити</DangerButton>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
