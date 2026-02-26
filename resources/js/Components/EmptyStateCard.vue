<script setup>
import Card from '@/Components/Card.vue'

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
  <Card padding="p-6" class="text-sm text-gray-700">
    <div v-if="title" class="font-medium">{{ title }}</div>
    <div v-if="description" class="mt-1 text-gray-600">{{ description }}</div>
    <div v-if="$slots.default" class="mt-3">
      <slot />
    </div>
  </Card>
</template>
