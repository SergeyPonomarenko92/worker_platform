<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    // Used across the app for both string and numeric inputs (via v-model.number).
    // Keep it permissive to avoid Vue type warnings for number/nullable fields.
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        v-bind="$attrs"
        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        v-model="model"
        ref="input"
    />
</template>
