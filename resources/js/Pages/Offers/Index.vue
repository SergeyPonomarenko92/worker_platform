<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    businessProfile: Object,
    offers: Array,
});
</script>

<template>
    <Head title="Пропозиції" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Пропозиції — {{ businessProfile.name }}
                </h2>
                <div class="flex gap-3">
                    <Link :href="route('dashboard.business-profiles.index')" class="text-sm text-indigo-600 hover:underline">Профілі бізнесу</Link>
                    <Link :href="route('dashboard.business-profiles.edit', businessProfile.id)" class="text-sm text-indigo-600 hover:underline">Профіль</Link>
                    <Link :href="route('dashboard.offers.create', businessProfile.id)">
                        <PrimaryButton>Створити пропозицію</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <div v-if="offers.length === 0" class="text-sm text-gray-700">Поки що немає пропозицій.</div>

                    <ul v-else class="divide-y divide-gray-200">
                        <li v-for="offer in offers" :key="offer.id" class="py-3 flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900">{{ offer.title }}</div>
                                <div class="text-sm text-gray-600">
                                    {{ offer.type }}
                                    <span v-if="offer.category">· {{ offer.category.name }}</span>
                                    <span v-if="offer.currency">· {{ offer.currency }}</span>
                                </div>
                            </div>
                            <Link :href="route('dashboard.offers.edit', [businessProfile.id, offer.id])" class="text-sm text-indigo-600 hover:underline">Редагувати</Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
