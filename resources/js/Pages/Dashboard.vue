<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Card from '@/Components/Card.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Дашборд</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Card>
                    <div class="text-gray-900 space-y-4">
                        <div>Ви увійшли в систему.</div>

                        <div class="flex flex-wrap items-center gap-3">
                            <Link :href="route('dashboard.business-profiles.index')">
                                <PrimaryButton>Профілі бізнесу</PrimaryButton>
                            </Link>

                            <Link
                                :href="route('dashboard.business-profiles.create')"
                                class="text-sm text-indigo-600 hover:underline"
                            >
                                Створити профіль
                            </Link>

                            <Link :href="route('catalog.index')" class="text-sm text-indigo-600 hover:underline">
                                Перейти до каталогу
                            </Link>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
