<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const success = computed(() => page.props.flash?.success);
const error = computed(() => page.props.flash?.error);
</script>

<template>
    <div class="space-y-3">
        <div
            v-if="success"
            role="status"
            aria-live="polite"
            aria-atomic="true"
            class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
        >
            {{ success }}
        </div>

        <div
            v-if="error"
            role="alert"
            aria-live="assertive"
            aria-atomic="true"
            class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
        >
            {{ error }}
        </div>
    </div>
</template>
