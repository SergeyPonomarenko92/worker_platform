<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Підтвердження email" />

        <div class="mb-4 text-sm text-gray-600">
            Дякуємо за реєстрацію! Перед початком роботи, будь ласка, підтвердьте вашу
            email-адресу, натиснувши на посилання з листа, який ми щойно надіслали.
            Якщо ви не отримали листа — ми надішлемо його ще раз.
        </div>

        <div
            class="mb-4 text-sm font-medium text-green-600"
            v-if="verificationLinkSent"
        >
            Нове посилання для підтвердження надіслано на email-адресу,
            вказану під час реєстрації.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Надіслати лист підтвердження ще раз
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >Вийти</Link
                >
            </div>
        </form>
    </GuestLayout>
</template>
