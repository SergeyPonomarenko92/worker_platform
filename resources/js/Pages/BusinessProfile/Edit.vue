<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    profile: Object,
});

const form = useForm({
    name: props.profile.name ?? '',
    about: props.profile.about ?? '',
    country_code: props.profile.country_code ?? 'UA',
    city: props.profile.city ?? '',
    address: props.profile.address ?? '',
    phone: props.profile.phone ?? '',
    website: props.profile.website ?? '',
    is_active: !!props.profile.is_active,
});

const submit = () => {
    form.patch(route('dashboard.business-profiles.update', props.profile.id));
};
</script>

<template>
    <Head title="Профіль бізнесу" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Профіль бізнесу
                </h2>
                <div class="flex gap-3">
                    <Link :href="route('dashboard.offers.index', props.profile.id)" class="text-sm text-indigo-600 hover:underline">Пропозиції</Link>
                    <Link :href="route('dashboard.portfolio-posts.index', props.profile.id)" class="text-sm text-indigo-600 hover:underline">Портфоліо</Link>
                    <Link :href="route('dashboard.stories.index', props.profile.id)" class="text-sm text-indigo-600 hover:underline">Історії</Link>
                    <Link :href="route('dashboard.deals.index', props.profile.id)" class="text-sm text-indigo-600 hover:underline">Угоди</Link>
                    <Link :href="route('dashboard.business-profiles.index')" class="text-sm text-indigo-600 hover:underline">До списку</Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="name" value="Назва" />
                            <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="about" value="Про нас" />
                            <textarea id="about" v-model="form.about" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" rows="5" />
                            <InputError class="mt-2" :message="form.errors.about" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="country_code" value="Код країни" />
                                <TextInput id="country_code" v-model="form.country_code" type="text" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.country_code" />
                            </div>
                            <div>
                                <InputLabel for="city" value="Місто" />
                                <TextInput id="city" v-model="form.city" type="text" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.city" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="address" value="Адреса" />
                            <TextInput id="address" v-model="form.address" type="text" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.address" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="phone" value="Телефон" />
                                <TextInput id="phone" v-model="form.phone" type="text" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.phone" />
                            </div>
                            <div>
                                <InputLabel for="website" value="Сайт" />
                                <TextInput id="website" v-model="form.website" type="text" class="mt-1 block w-full" />
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
                            <PrimaryButton :disabled="form.processing">Зберегти</PrimaryButton>
                            <span v-if="form.recentlySuccessful" class="text-sm text-gray-600">Збережено.</span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
