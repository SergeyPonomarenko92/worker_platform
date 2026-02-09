<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    profiles: Array,
});
</script>

<template>
    <Head title="Профілі бізнесу" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Профілі бізнесу</h2>
                <Link :href="route('dashboard.business-profiles.create')">
                    <PrimaryButton>Створити профіль</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <div v-if="profiles.length === 0" class="text-sm text-gray-700">
                        Поки що немає профілів.
                    </div>

                    <ul v-else class="divide-y divide-gray-200">
                        <li v-for="p in profiles" :key="p.id" class="py-3 flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900">{{ p.name }}</div>
                                <div class="text-sm text-gray-600">/{{ p.slug }}</div>
                            </div>
                            <div class="flex items-center gap-4 text-sm">
                                <Link :href="route('dashboard.offers.index', p.id)" class="text-indigo-600 hover:underline">Пропозиції</Link>
                                <Link :href="route('dashboard.business-profiles.edit', p.id)" class="text-indigo-600 hover:underline">Редагувати</Link>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
