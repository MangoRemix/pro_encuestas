<template>
    <div class="mt-4 flex items-center justify-center gap-4 text-white">
        <button
            :disabled="currentPage === 1"
            @click="$emit('page-change', currentPage - 1)"
            class="rounded bg-blue-800/50 px-3 py-1 transition-colors hover:bg-blue-800 disabled:opacity-50"
        >
            <Icon icon="ic:baseline-chevron-left" class="text-xl" />
        </button>

        <span class="text-sm font-medium">
            Página {{ currentPage }} de {{ lastPage }} ({{ from }} - {{ to }} de
            {{ total }})
        </span>

        <button
            :disabled="currentPage === lastPage"
            @click="$emit('page-change', currentPage + 1)"
            class="rounded bg-blue-800/50 px-3 py-1 transition-colors hover:bg-blue-800 disabled:opacity-50"
        >
            <Icon icon="ic:baseline-chevron-right" class="text-xl" />
        </button>
    </div>
</template>

<script setup>
import { Icon } from '@iconify/vue';

defineProps({
    currentPage: Number,
    lastPage: Number,
    total: Number,
    from: Number,
    to: Number,
});

defineEmits(['page-change']);
</script>
