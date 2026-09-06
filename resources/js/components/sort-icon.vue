<script setup>
import { Icon } from '@iconify/vue';
import { computed } from 'vue';

const props = defineProps({
    field: String,
    currentField: String,
    direction: String,
});

const isActive = computed(() => props.field === props.currentField);
const icon = computed(() => {
    if (!isActive.value) {
return 'ic:baseline-unfold-more';
}

    return props.direction === 'asc' ? 'ic:baseline-expand-less' : 'ic:baseline-expand-more';
});
</script>

<template>
    <Icon :icon="icon" class="text-slate-400 transition-colors" :class="{ 'text-blue-400': isActive }" />
</template>
