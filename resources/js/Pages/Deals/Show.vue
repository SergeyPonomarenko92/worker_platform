<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    businessProfile: Object,
    deal: Object,
});

const form = useForm({});

const statusLabel = (status) => {
    switch (status) {
        case 'draft':
            return 'Чернетка';
        case 'in_progress':
            return 'В процесі';
        case 'completed':
            return 'Завершено';
        case 'cancelled':
            return 'Скасовано';
        default:
            return status;
    }
};

const markInProgress = () => form.patch(route('dashboard.deals.in-progress', [props.businessProfile.id, props.deal.id]));
const markCompleted = () => form.patch(route('dashboard.deals.completed', [props.businessProfile.id, props.deal.id]));
const markCancelled = () => form.patch(route('dashboard.deals.cancelled', [props.businessProfile.id, props.deal.id]));
</script>

<template>
    <Head :title="`Угода #${deal.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Угода #{{ deal.id }} — {{ businessProfile.name }}
                </h2>
                <div class="flex gap-3">
                    <Link :href="route('dashboard.deals.index', businessProfile.id)" class="text-sm text-indigo-600 hover:underline">До угод</Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg space-y-4">
                    <div class="text-sm text-gray-700">
                        <div><span class="font-medium">Статус:</span> {{ statusLabel(deal.status) }}</div>
                        <div v-if="deal.client"><span class="font-medium">Клієнт:</span> {{ deal.client.name }} ({{ deal.client.email }})</div>
                        <div v-if="deal.offer"><span class="font-medium">Офер:</span> {{ deal.offer.title }}</div>
                        <div v-if="deal.agreed_price"><span class="font-medium">Ціна:</span> {{ deal.agreed_price }} {{ deal.currency }}</div>
                        <div v-if="deal.completed_at"><span class="font-medium">Завершено:</span> {{ new Date(deal.completed_at).toLocaleString() }}</div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <SecondaryButton type="button" @click="markInProgress" :disabled="form.processing">В процесі</SecondaryButton>
                        <PrimaryButton type="button" @click="markCompleted" :disabled="form.processing">Завершити</PrimaryButton>
                        <SecondaryButton type="button" @click="markCancelled" :disabled="form.processing">Скасувати</SecondaryButton>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
