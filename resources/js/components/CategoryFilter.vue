<template>
    <div class="flex flex-wrap gap-2 py-4">
        <button
            @click="$emit('update:modelValue', null)"
            :class="[
                'px-4 py-1.5 rounded-full text-sm font-medium transition-all duration-200 border',
                !modelValue 
                    ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' 
                    : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-400'
            ]"
        >
            Todas
        </button>

        <button
            v-for="category in categories"
            :key="category.id"
            @click="$emit('update:modelValue', category.name)"
            :class="[
                'px-4 py-1.5 rounded-full text-sm font-medium transition-all duration-200 border',
                modelValue === category.name 
                    ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' 
                    : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-400'
            ]"
        >
            {{ category.name }}
        </button>
    </div>
</template>

<script setup>
defineProps({
    categories: { type: Array, required: true },
    modelValue: { type: [String, null], default: null }
});

defineEmits(['update:modelValue']);
</script>
