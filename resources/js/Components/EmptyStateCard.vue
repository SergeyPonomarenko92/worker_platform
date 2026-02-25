<script setup>
defineProps({
  title: {
    type: String,
    default: '',
  },
  description: {
    type: String,
    default: '',
  },
})
</script>

<template>
  <div class="rounded-lg border border-gray-200 bg-white p-6 text-sm text-gray-700">
    <div v-if="title" class="font-medium">{{ title }}</div>
    <div v-if="description" class="mt-1 text-gray-600">{{ description }}</div>
    <div v-if="$slots.default" class="mt-3">
      <slot />
    </div>
  </div>
</template>
