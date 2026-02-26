<script setup>
import { computed } from 'vue'

const props = defineProps({
  as: {
    type: String,
    default: 'div',
  },
  padding: {
    type: String,
    default: 'p-4',
  },
})

const baseClass = computed(() => ['rounded-lg border border-gray-200 bg-white', props.padding])
</script>

<template>
  <component :is="as" :class="baseClass">
    <slot />
  </component>
</template>
