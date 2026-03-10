<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    businessProfile: {
        type: Object,
        required: true,
    },
    active: {
        type: String,
        default: null,
        // offers|portfolio|stories|deals|profile|profiles
    },
})

const isActive = (key) => props.active && props.active === key

const ariaCurrent = (key) => (isActive(key) ? 'page' : null)

const linkClass = (key) => {
    const base = 'text-sm rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2'

    if (isActive(key)) {
        return base + ' text-gray-900 font-medium'
    }

    return base + ' text-indigo-600 hover:underline'
}
</script>

<template>
    <div class="flex gap-3 flex-wrap justify-end">
        <Link
            :href="route('dashboard.business-profiles.index')"
            :class="linkClass('profiles')"
            :aria-current="ariaCurrent('profiles')"
        >
            Профілі бізнесу
        </Link>

        <Link
            :href="route('dashboard.business-profiles.edit', businessProfile.id)"
            :class="linkClass('profile')"
            :aria-current="ariaCurrent('profile')"
        >
            Профіль
        </Link>

        <Link
            :href="route('dashboard.offers.index', businessProfile.id)"
            :class="linkClass('offers')"
            :aria-current="ariaCurrent('offers')"
        >
            Пропозиції
        </Link>

        <Link
            :href="route('dashboard.portfolio-posts.index', businessProfile.id)"
            :class="linkClass('portfolio')"
            :aria-current="ariaCurrent('portfolio')"
        >
            Портфоліо
        </Link>

        <Link
            :href="route('dashboard.stories.index', businessProfile.id)"
            :class="linkClass('stories')"
            :aria-current="ariaCurrent('stories')"
        >
            Історії
        </Link>

        <Link
            :href="route('dashboard.deals.index', businessProfile.id)"
            :class="linkClass('deals')"
            :aria-current="ariaCurrent('deals')"
        >
            Угоди
        </Link>
    </div>
</template>
