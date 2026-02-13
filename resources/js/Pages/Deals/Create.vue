<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    businessProfile: Object,
    offers: Array,
});

const form = useForm({
    client_email: '',
    offer_id: null,
    agreed_price: '',
    currency: 'UAH',
    status: 'draft',
});

const submit = () => {
    form.post(route('dashboard.deals.store', props.businessProfile.id));
};
</script>

<template>
    <Head title="Створити угоду" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Нова угода — {{ businessProfile.name }}
                </h2>
                <div class="flex gap-3">
                    <Link :href="route('dashboard.deals.index', businessProfile.id)" class="text-sm text-indigo-600 hover:underline">До угод</Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="client_email" value="Email клієнта (має бути зареєстрований)" />
                            <TextInput id="client_email" v-model="form.client_email" type="email" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="form.errors.client_email" />
                        </div>

                        <div>
                            <InputLabel for="offer_id" value="Офер (необов'язково)" />
                            <select id="offer_id" v-model="form.offer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option :value="null">—</option>
                                <option v-for="o in offers" :key="o.id" :value="o.id">{{ o.title }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.offer_id" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="agreed_price" value="Узгоджена ціна (необов'язково)" />
                                <TextInput id="agreed_price" v-model.number="form.agreed_price" type="number" min="0" step="1" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.agreed_price" />
                            </div>
                            <div>
                                <InputLabel for="currency" value="Валюта" />
                                <TextInput id="currency" v-model="form.currency" type="text" class="mt-1 block w-full" minlength="3" maxlength="3" required />
                                <InputError class="mt-2" :message="form.errors.currency" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="status" value="Початковий статус" />
                            <select id="status" v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="draft">Чернетка</option>
                                <option value="in_progress">В процесі</option>
                                <option value="completed">Завершено</option>
                                <option value="cancelled">Скасовано</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.status" />
                        </div>

                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing">Створити</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
