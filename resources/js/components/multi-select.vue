<template>
    <div ref="root" class="relative">
        <button
            type="button"
            class="inputs-form flex w-full cursor-pointer items-center justify-between bg-white text-left text-gray-900"
            @click="isOpen = !isOpen"
        >
            <span class="truncate">{{ summaryLabel }}</span>
            <svg
                class="ml-2 h-4 w-4 shrink-0 text-gray-500"
                :class="{ 'rotate-180': isOpen }"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path
                    fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                    clip-rule="evenodd"
                />
            </svg>
        </button>

        <div
            v-if="isOpen"
            class="absolute z-20 mt-1 max-h-64 w-full min-w-56 overflow-y-auto rounded-lg border border-slate-700 bg-slate-800 py-1 shadow-xl"
        >
            <label
                class="flex cursor-pointer items-center gap-2 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-700"
            >
                <input
                    type="checkbox"
                    class="h-4 w-4 rounded border-slate-500"
                    :checked="allSelected"
                    :indeterminate="someSelected"
                    @change="toggleAll"
                />
                Seleccionar todas
            </label>
            <div class="my-1 border-t border-slate-700"></div>
            <label
                v-for="option in options"
                :key="option.id"
                class="flex cursor-pointer items-center gap-2 px-3 py-2 text-sm text-slate-200 hover:bg-slate-700"
            >
                <input
                    type="checkbox"
                    class="h-4 w-4 rounded border-slate-500"
                    :value="option.id"
                    :checked="selectedIds.has(option.id)"
                    @change="toggleOne(option.id)"
                />
                {{ option.name }}
            </label>
            <div
                v-if="options.length === 0"
                class="px-3 py-2 text-sm text-slate-400 italic"
            >
                No hay opciones disponibles.
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    options: { type: Array, required: true },
    placeholder: { type: String, default: 'Selecciona una o más opciones' },
    allLabel: { type: String, default: 'Todas' },
});

const emit = defineEmits(['update:modelValue']);

const root = ref(null);
const isOpen = ref(false);

const selectedIds = computed(() => new Set(props.modelValue));

const allSelected = computed(
    () =>
        props.options.length > 0 &&
        props.options.every((option) => selectedIds.value.has(option.id)),
);

const someSelected = computed(
    () => !allSelected.value && props.modelValue.length > 0,
);

const summaryLabel = computed(() => {
    if (props.modelValue.length === 0) {
        return props.placeholder;
    }

    if (allSelected.value) {
        return props.allLabel;
    }

    if (props.modelValue.length === 1) {
        const selected = props.options.find(
            (option) => option.id === props.modelValue[0],
        );

        return selected?.name || props.placeholder;
    }

    return `${props.modelValue.length} parroquias seleccionadas`;
});

const toggleAll = () => {
    if (allSelected.value) {
        emit('update:modelValue', []);

        return;
    }

    emit(
        'update:modelValue',
        props.options.map((option) => option.id),
    );
};

const toggleOne = (id) => {
    const next = new Set(selectedIds.value);

    if (next.has(id)) {
        next.delete(id);
    } else {
        next.add(id);
    }

    emit('update:modelValue', [...next]);
};

const handleClickOutside = (event) => {
    if (root.value && !root.value.contains(event.target)) {
        isOpen.value = false;
    }
};

onMounted(() => document.addEventListener('click', handleClickOutside));
onBeforeUnmount(() =>
    document.removeEventListener('click', handleClickOutside),
);
</script>
