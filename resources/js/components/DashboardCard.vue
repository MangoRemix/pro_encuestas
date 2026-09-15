<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
});
</script>

<template>
    <div
        class="flex flex-col justify-between rounded-lg border border-slate-200 bg-white/90 p-6 shadow-sm"
    >
        <h3
            class="self-start text-sm font-medium tracking-wider text-slate-500 uppercase"
        >
            {{ title }}
        </h3>
        <div
            class="mt-4 text-center text-3xl font-bold text-slate-900 md:text-4xl lg:text-5xl"
        >
            {{ value }}
        </div>
    </div>
</template>
