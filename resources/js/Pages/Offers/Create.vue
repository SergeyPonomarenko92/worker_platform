<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import BusinessProfileSectionNav from '@/Components/BusinessProfileSectionNav.vue';
import { normalizeCurrencyCode } from '@/lib/normalizers';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    businessProfile: Object,
    categories: Array,
});

const form = useForm({
    category_id: null,
    type: 'service',
    title: '',
    description: '',
    price_from: null,
    price_to: null,
    currency: 'UAH',
    is_active: true,
});

const normalizeCurrency = () => {
    form.currency = normalizeCurrencyCode(form.currency);
};

const submit = () => {
    normalizeCurrency();
    form.post(route('dashboard.offers.store', props.businessProfile.id));
};
</script>

<template>
    <Head title="Створити пропозицію" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <Breadcrumbs :items="[
                        { label: 'Кабінет', href: route('dashboard') },
                        { label: 'Профілі бізнесу', href: route('dashboard.business-profiles.index') },
                        { label: props.businessProfile.name, href: route('dashboard.business-profiles.edit', props.businessProfile.id) },
                        { label: 'Пропозиції', href: route('dashboard.offers.index', props.businessProfile.id) },
                        { label: 'Створити', current: true },
                    ]" />
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">Створити пропозицію</h2>
                </div>
                <div class="flex items-center gap-3 flex-wrap justify-end">
                    <BusinessProfileSectionNav :business-profile="props.businessProfile" active="offers" />
                    <Link :href="route('dashboard.offers.index', props.businessProfile.id)" class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">Назад</Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <form @submit.prevent="submit" novalidate class="space-y-6">
                        <div>
                            <InputLabel for="type" value="Тип" />
                            <select id="type" v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="service">Послуга</option>
                                <option value="product">Товар</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.type" />
                        </div>

                        <div>
                            <InputLabel for="title" value="Назва" />
                            <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.title" />
                        </div>

                        <div>
                            <InputLabel for="category_id" value="Категорія" />
                            <select id="category_id" v-model="form.category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option :value="null">—</option>
                                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.label || c.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.category_id" />
                        </div>

                        <div>
                            <InputLabel for="description" value="Опис" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                rows="5"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <InputLabel for="price_from" value="Ціна від" />
                                <TextInput id="price_from" v-model.number="form.price_from" type="number" inputmode="numeric" autocomplete="off" step="1" min="0" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.price_from" />
                            </div>
                            <div>
                                <InputLabel for="price_to" value="Ціна до" />
                                <TextInput id="price_to" v-model.number="form.price_to" type="number" inputmode="numeric" autocomplete="off" step="1" min="0" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.price_to" />
                            </div>
                            <div>
                                <InputLabel for="currency" value="Валюта" />
                                <TextInput
                                    id="currency"
                                    v-model="form.currency"
                                    type="text"
                                    inputmode="text"
                                    autocapitalize="characters"
                                    spellcheck="false"
                                    autocomplete="off"
                                    aria-describedby="currency-hint"
                                    class="mt-1 block w-full"
                                    minlength="3"
                                    maxlength="3"
                                    @blur="normalizeCurrency"
                                />
                                <InputError class="mt-2" :message="form.errors.currency" />
                                <div id="currency-hint" class="mt-1 text-xs text-gray-500">Напр.: UAH</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300" />
                                Активна
                            </label>
                        </div>

                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing">Створити</PrimaryButton>
                            <span v-if="form.recentlySuccessful" class="text-sm text-gray-600">Збережено.</span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
