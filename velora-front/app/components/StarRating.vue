<script setup>
/**
 * Half-fillable 5-star rating widget.
 *
 * Intentionally unstyled: the surrounding page owns the look through its own
 * `:deep(.star-wrap)` / `:deep(.star-svg)` rules, and `rootClass` lets each
 * caller keep the root selector it already styles.
 */
const props = defineProps({
    score: { type: Number, default: 0 },
    interactive: { type: Boolean, default: false },
    rootClass: { type: String, default: 'stars' },
})

const emit = defineEmits(['rate'])

const STAR_POINTS =
    '12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26'

const hovered = ref(null)

/**
 * Fill percentage per star: the hover preview when the widget is interactive
 * and the pointer is over it, otherwise the partial fill from `score`.
 */
const fills = computed(() => {
    const preview = props.interactive ? hovered.value : null

    return Array.from({ length: 5 }, (_, i) =>
        preview !== null
            ? (i < preview ? 100 : 0)
            : Math.min(Math.max(props.score - i, 0), 1) * 100
    )
})

function onEnter(index) {
    if (props.interactive) hovered.value = index + 1
}

function onLeave() {
    if (props.interactive) hovered.value = null
}

function onClick(index) {
    if (props.interactive) emit('rate', index + 1)
}
</script>

<template>
    <div :class="rootClass">
        <button v-for="(fill, i) in fills" :key="i" class="star-wrap" @click="onClick(i)" @mouseenter="onEnter(i)"
            @mouseleave="onLeave">
            <svg class="star-svg" viewBox="0 0 24 24">
                <polygon :points="STAR_POINTS" fill="#E8DDD0" stroke="none" />
            </svg>
            <div class="star-fill" :style="{ width: `${fill}%` }">
                <svg class="star-svg" viewBox="0 0 24 24">
                    <polygon :points="STAR_POINTS" fill="#C9A96E" stroke="none" />
                </svg>
            </div>
        </button>
    </div>
</template>
