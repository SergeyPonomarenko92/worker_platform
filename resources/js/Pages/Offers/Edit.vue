<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    businessProfile: Object,
    offer: Object,
    categories: Array,
});

const form = useForm({
    category_id: props.offer.category_id ?? null,
    type: props.offer.type ?? 'service',
    title: props.offer.title ?? '',
    description: props.offer.description ?? '',
    price_from: props.offer.price_from ?? null,
    price_to: props.offer.price_to ?? null,
    currency: props.offer.currency ?? 'UAH',
    is_active: !!props.offer.is_active,
});

const normalizeCurrency = () => {
    form.currency = (form.currency || '').toUpperCase().slice(0, 3);
};

const submit = () => {
    normalizeCurrency();
    form.patch(route('dashboard.offers.update', [props.businessProfile.id, props.offer.id]));
};

const destroy = () => {
    if (!confirm('Видалити цю пропозицію?')) return;
    form.delete(route('dashboard.offers.destroy', [props.businessProfile.id, props.offer.id]));
};
</script>

<template>
    <Head title="Редагувати пропозицію" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Редагувати пропозицію</h2>
                <Link :href="route('dashboard.offers.index', props.businessProfile.id)" class="text-sm text-indigo-600 hover:underline">Назад</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="type" value="Тип" />
                            <select id="type" v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="service">Послуга</option>
                                <option value="product">Товар</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.type" />
                        </div>

                        <div>
                            <InputLabel for="title" value="Назва" />
                            <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="form.errors.title" />
                        </div>

                        <div>
                            <InputLabel for="category_id" value="Категорія" />
                            <select id="category_id" v-model="form.category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option :value="null">—</option>
                                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.category_id" />
                        </div>

                        <div>
                            <InputLabel for="description" value="Опис" />
                            <textarea id="description" v-model="form.description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" rows="5" />
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <InputLabel for="price_from" value="Ціна від" />
                                <TextInput id="price_from" v-model.number="form.price_from" type="number" step="1" min="0" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.price_from" />
                            </div>
                            <div>
                                <InputLabel for="price_to" value="Ціна до" />
                                <TextInput id="price_to" v-model.number="form.price_to" type="number" step="1" min="0" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.price_to" />
                            </div>
                            <div>
                                <InputLabel for="currency" value="Валюта" />
                                <TextInput id="currency" v-model="form.currency" type="text" class="mt-1 block w-full" minlength="3" maxlength="3" @blur="normalizeCurrency" required />
                                <InputError class="mt-2" :message="form.errors.currency" />
                            <div class="mt-1 text-xs text-gray-500">Напр.: UAH</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300" />
                                Активна
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Зберегти</PrimaryButton>
                                <span v-if="form.recentlySuccessful" class="text-sm text-gray-600">Збережено.</span>
                            </div>
                            <DangerButton type="button" @click="destroy">Видалити</DangerButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
