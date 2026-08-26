<template>
    <div class="flex flex-wrap gap-2 mb-3">
        <button
            v-if="categories.length>0"
            @click="$emit('update:modelValue', null)"
            :class="[
                'px-4 py-1.5 rounded-full text-sm font-medium transition-all duration-200 border',
                !modelValue 
                    ? 'bg-blue-800 text-white border-blue-800 shadow-md' 
                    : 'bg-white text-slate-600 border-slate-200 hover:border-blue-400'
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
                    ? 'bg-blue-800 text-white border-blue-800 shadow-md' 
                    : 'bg-white text-slate-600 border-slate-200 hover:border-blue-400'
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
