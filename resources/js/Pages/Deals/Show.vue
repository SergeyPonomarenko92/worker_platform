<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
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

const canMarkInProgress = () => props.deal.status !== 'in_progress' && props.deal.status !== 'completed' && props.deal.status !== 'cancelled';
const canMarkCompleted = () => props.deal.status !== 'completed' && props.deal.status !== 'cancelled';
const canMarkCancelled = () => props.deal.status !== 'cancelled' && props.deal.status !== 'completed';

const markInProgress = () => {
    if (!canMarkInProgress()) return;
    form.patch(route('dashboard.deals.in-progress', [props.businessProfile.id, props.deal.id]));
};

const markCompleted = () => {
    if (!canMarkCompleted()) return;
    if (!confirm('Позначити угоду як завершену?')) return;

    form.patch(route('dashboard.deals.completed', [props.businessProfile.id, props.deal.id]));
};

const markCancelled = () => {
    if (!canMarkCancelled()) return;
    if (!confirm('Скасувати угоду?')) return;

    form.patch(route('dashboard.deals.cancelled', [props.businessProfile.id, props.deal.id]));
};
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
                    <Link :href="route('dashboard.deals.index', businessProfile.id)" class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">До угод</Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg space-y-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="text-sm text-gray-700 space-y-1">
                            <div>
                                <span class="font-medium">Статус:</span>
                                <span class="ml-1">{{ statusLabel(deal.status) }}</span>
                            </div>
                            <div v-if="deal.client">
                                <span class="font-medium">Клієнт:</span>
                                <span class="ml-1">{{ deal.client.name }} ({{ deal.client.email }})</span>
                            </div>
                            <div v-if="deal.offer">
                                <span class="font-medium">Офер:</span>
                                <span class="ml-1">{{ deal.offer.title }}</span>
                            </div>
                        </div>

                        <div class="text-sm text-gray-700 space-y-1">
                            <div v-if="deal.agreed_price">
                                <span class="font-medium">Ціна:</span>
                                <span class="ml-1">{{ deal.agreed_price }} {{ deal.currency }}</span>
                            </div>
                            <div v-else class="text-gray-500">Ціна: —</div>

                            <div v-if="deal.completed_at">
                                <span class="font-medium">Завершено:</span>
                                <span class="ml-1">{{ new Date(deal.completed_at).toLocaleString('uk-UA') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <SecondaryButton
                            type="button"
                            @click="markInProgress"
                            :disabled="form.processing || !canMarkInProgress()"
                        >
                            В процесі
                        </SecondaryButton>

                        <PrimaryButton
                            type="button"
                            @click="markCompleted"
                            :disabled="form.processing || !canMarkCompleted()"
                        >
                            Завершити
                        </PrimaryButton>

                        <DangerButton
                            type="button"
                            @click="markCancelled"
                            :disabled="form.processing || !canMarkCancelled()"
                        >
                            Скасувати
                        </DangerButton>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
