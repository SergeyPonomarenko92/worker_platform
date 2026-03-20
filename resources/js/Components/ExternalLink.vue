<script setup>
import { computed, useAttrs } from 'vue'

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
})

const attrs = useAttrs()

const mergedRel = computed(() => {
    const fromAttrs = typeof attrs.rel === 'string' ? attrs.rel.trim() : ''

    // Ensure security defaults are always present.
    const defaults = ['noopener', 'noreferrer', 'nofollow']
    const tokens = new Set(
        (fromAttrs ? fromAttrs.split(/\s+/) : []).filter(Boolean).concat(defaults)
    )

    return Array.from(tokens).join(' ')
})
</script>

<template>
    <a
        v-bind="attrs"
        :href="href"
        target="_blank"
        referrerpolicy="no-referrer"
        :rel="mergedRel"
    >
        <slot />
    </a>
</template>
