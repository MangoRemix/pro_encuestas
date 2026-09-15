<template>
    <div class="mb-3 flex flex-wrap gap-2">
        <button
            v-if="categories.length > 0"
            @click="$emit('update:modelValue', null)"
            :class="[
                'rounded-full border px-4 py-1.5 text-sm font-medium transition-all duration-200',
                !modelValue
                    ? 'border-blue-800 bg-blue-800 text-white shadow-md'
                    : 'border-slate-200 bg-white text-slate-600 hover:border-blue-400',
            ]"
        >
            Todas
        </button>

        <button
            v-for="category in categories"
            :key="category.id"
            @click="$emit('update:modelValue', category.name)"
            :class="[
                'rounded-full border px-4 py-1.5 text-sm font-medium transition-all duration-200',
                modelValue === category.name
                    ? 'border-blue-800 bg-blue-800 text-white shadow-md'
                    : 'border-slate-200 bg-white text-slate-600 hover:border-blue-400',
            ]"
        >
            {{ category.name }}
        </button>
    </div>
</template>

<script setup>
defineProps({
    categories: { type: Array, required: true },
    modelValue: { type: [String, null], default: null },
});

defineEmits(['update:modelValue']);
</script>
