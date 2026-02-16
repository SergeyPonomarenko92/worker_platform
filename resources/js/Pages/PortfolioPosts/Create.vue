<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    businessProfile: Object,
});

const form = useForm({
    title: '',
    body: '',
    published_at: '',
});

const publishNow = () => {
    const now = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    form.published_at = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
};

const makeDraft = () => {
    form.published_at = '';
};

const submit = () => {
    form.post(route('dashboard.portfolio-posts.store', props.businessProfile.id));
};
</script>

<template>
    <Head title="Створити пост" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Новий пост — {{ businessProfile.name }}
                </h2>
                <div class="flex gap-3">
                    <Link :href="route('dashboard.portfolio-posts.index', businessProfile.id)" class="text-sm text-indigo-600 hover:underline">До портфоліо</Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <form @submit.prevent="submit" novalidate class="space-y-6">
                        <div>
                            <InputLabel for="title" value="Заголовок" />
                            <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.title" />
                        </div>

                        <div>
                            <InputLabel for="body" value="Текст" />
                            <textarea id="body" v-model="form.body" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" rows="8" />
                            <InputError class="mt-2" :message="form.errors.body" />
                        </div>

                        <div>
                            <InputLabel for="published_at" value="Дата публікації (необов'язково)" />
                            <TextInput id="published_at" v-model="form.published_at" type="datetime-local" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.published_at" />
                            <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-gray-500">
                                <span>Час береться з вашого браузера.</span>
                                <button type="button" class="text-indigo-600 hover:underline" @click="publishNow">Опублікувати зараз</button>
                                <button type="button" class="text-indigo-600 hover:underline" @click="makeDraft">Зробити чернеткою</button>
                            </div>
                            <div class="mt-1 text-xs text-gray-500">Якщо не вказати — пост вважається чернеткою.</div>
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
