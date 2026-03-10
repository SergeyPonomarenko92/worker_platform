<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    items: {
        type: Array,
        required: true,
        // [{ label: string, href?: string|null, current?: boolean }]
    },
})
</script>

<template>
    <nav aria-label="Breadcrumb" class="mb-2">
        <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-600">
            <li v-for="(item, idx) in items" :key="idx" class="flex items-center">
                <span v-if="idx !== 0" class="mx-2 text-gray-300" aria-hidden="true">/</span>

                <Link
                    v-if="item.href && !item.current"
                    :href="item.href"
                    class="text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded"
                >
                    {{ item.label }}
                </Link>

                <span v-else :aria-current="item.current ? 'page' : null" class="text-gray-700">
                    {{ item.label }}
                </span>
            </li>
        </ol>
    </nav>
</template>
