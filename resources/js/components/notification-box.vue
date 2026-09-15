<script setup>
const { message, isError } = defineProps(['message', 'isError']);
</script>
<template>
    <div
        v-if="message"
        :class="[
            'mt-4 rounded-md p-3 text-center text-sm font-medium transition-all',
            isError
                ? 'border border-red-200 bg-red-50 text-red-700'
                : 'border border-emerald-200 bg-emerald-50 text-emerald-700',
        ]"
    >
        {{ message }}
    </div>
</template>
