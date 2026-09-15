<script setup>
defineProps({
    items: { type: Array, required: true },
    current: { type: String, required: true },
});
</script>

<template>
    <nav aria-label="Progreso" class="mb-8 w-full">
        <ol role="list" class="flex w-full items-center justify-between">
            <li
                v-for="(item, index) in items"
                :key="index"
                class="relative flex flex-1 flex-col items-center"
            >
                <!-- Línea conectora -->
                <div
                    v-if="index !== items.length - 1"
                    class="absolute top-3 left-[50%] h-0.5 w-full"
                    :class="
                        items.indexOf(current) > index
                            ? 'bg-blue-600'
                            : 'bg-gray-200'
                    "
                ></div>

                <!-- Círculo de paso -->
                <div
                    class="relative z-10 flex h-6 w-6 items-center justify-center rounded-full text-xs font-medium ring-4 ring-white transition-colors duration-300"
                    :class="[
                        current === item
                            ? 'bg-blue-600 text-white'
                            : items.indexOf(current) > index
                              ? 'bg-blue-600 text-white'
                              : 'border border-gray-300 bg-gray-100 text-slate-700',
                    ]"
                >
                    <!-- Checkmark para pasos completados -->
                    <svg
                        v-if="items.indexOf(current) > index"
                        class="h-3.5 w-3.5 text-white"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <span v-else>{{ index + 1 }}</span>
                </div>

                <!-- Etiqueta del paso -->
                <span
                    class="mt-3 text-center text-xs font-semibold tracking-wide uppercase transition-colors duration-300"
                    :class="
                        current === item
                            ? 'text-yellow-500'
                            : 'text-neutral-400'
                    "
                >
                    {{ item }}
                </span>
            </li>
        </ol>
    </nav>
</template>
