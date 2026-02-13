<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    businessProfile: Object,
    defaultExpiresAt: String,
});

const form = useForm({
    media_path: '',
    caption: '',
    expires_at: props.defaultExpiresAt ? props.defaultExpiresAt.slice(0, 16) : '',
});

const submit = () => {
    form.post(route('dashboard.stories.store', props.businessProfile.id));
};
</script>

<template>
    <Head title="Створити історію" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Нова історія — {{ businessProfile.name }}
                </h2>
                <div class="flex gap-3">
                    <Link :href="route('dashboard.stories.index', businessProfile.id)" class="text-sm text-indigo-600 hover:underline">До історій</Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="media_path" value="Шлях до медіа (поки що вручну)" />
                            <TextInput id="media_path" v-model="form.media_path" type="text" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="form.errors.media_path" />
                        </div>

                        <div>
                            <InputLabel for="caption" value="Підпис" />
                            <textarea id="caption" v-model="form.caption" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" rows="4" />
                            <InputError class="mt-2" :message="form.errors.caption" />
                        </div>

                        <div>
                            <InputLabel for="expires_at" value="Дата завершення" />
                            <TextInput id="expires_at" v-model="form.expires_at" type="datetime-local" class="mt-1 block w-full" required />
                            <div class="mt-1 text-xs text-gray-500">Час береться з вашого браузера.</div>
                            <InputError class="mt-2" :message="form.errors.expires_at" />
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
