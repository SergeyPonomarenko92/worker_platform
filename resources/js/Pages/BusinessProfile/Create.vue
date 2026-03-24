<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { normalizeCountryCode } from '@/lib/normalizers';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    about: '',
    country_code: 'UA',
    city: '',
    address: '',
    phone: '',
    website: '',
    is_active: true,
});

const normalizeCountry = () => {
    form.country_code = normalizeCountryCode(form.country_code);
};

const submit = () => {
    normalizeCountry();
    form.post(route('dashboard.business-profiles.store'));
};
</script>

<template>
    <Head title="Профіль бізнесу" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <Breadcrumbs :items="[
                        { label: 'Кабінет', href: route('dashboard') },
                        { label: 'Профілі бізнесу', href: route('dashboard.business-profiles.index') },
                        { label: 'Створити', current: true },
                    ]" />
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Створити профіль бізнесу
                    </h2>
                </div>
                <Link :href="route('dashboard.business-profiles.index')" class="text-sm text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">До списку</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <form @submit.prevent="submit" novalidate class="space-y-6">
                        <div>
                            <InputLabel for="name" value="Назва" />
                            <TextInput id="name" v-model="form.name" type="text" autocomplete="organization" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="about" value="Про нас" />
                            <textarea id="about" v-model="form.about" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" rows="5"></textarea>
                            <InputError class="mt-2" :message="form.errors.about" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="country_code" value="Код країни" />
                                <TextInput id="country_code" v-model="form.country_code" type="text" inputmode="text" autocomplete="country" autocapitalize="characters" class="mt-1 block w-full" maxlength="2" @blur="normalizeCountry" />
                                <p class="mt-1 text-xs text-gray-500">Напр.: <span class="font-medium">UA</span> (значення буде нормалізовано до ISO-коду).</p>
                                <InputError class="mt-2" :message="form.errors.country_code" />
                            </div>
                            <div>
                                <InputLabel for="city" value="Місто" />
                                <TextInput id="city" v-model="form.city" type="text" autocomplete="address-level2" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.city" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="address" value="Адреса" />
                            <TextInput id="address" v-model="form.address" type="text" autocomplete="street-address" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.address" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="phone" value="Телефон" />
                                <TextInput id="phone" v-model="form.phone" type="tel" inputmode="tel" autocomplete="tel" class="mt-1 block w-full" />
                                <p class="mt-1 text-xs text-gray-500">Можна з пробілами: <span class="font-medium">+380 50 123 45 67</span>.</p>
                                <InputError class="mt-2" :message="form.errors.phone" />
                            </div>
                            <div>
                                <InputLabel for="website" value="Сайт" />
                                <TextInput id="website" v-model="form.website" type="url" inputmode="url" autocomplete="url" class="mt-1 block w-full" />
                                <p class="mt-1 text-xs text-gray-500">Можна без протоколу: <span class="font-medium">example.com</span> (додамо https:// автоматично).</p>
                                <InputError class="mt-2" :message="form.errors.website" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300" />
                                Активний профіль
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
