<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 space-y-3">
                        <div>You're logged in!</div>
                        <div class="flex gap-4 text-sm">
                            <Link :href="route('dashboard.business-profile.edit')" class="text-indigo-600 hover:underline">
                                Business profile
                            </Link>
                            <Link :href="route('dashboard.offers.index')" class="text-indigo-600 hover:underline">
                                Offers
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
