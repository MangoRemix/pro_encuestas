<template>
    <div class="rounded-xl border border-neutral-200 bg-white p-2 shadow-sm">
        <h3 class="mb-6 text-lg font-semibold text-gray-800">
            {{ question.order }}. {{ question.name?.toUpperCase() }}
        </h3>

        <div
            class="flex scrollbar-thumb-blue-800 scrollbar-track-white/30 flex-col gap-y-3 overflow-y-scroll lg:max-h-66"
        >
            <div
                v-for="answer in answers"
                :key="answer.id"
                @click="selectedAnswer = answer.id"
                class="answer-option flex min-h-10 w-full cursor-pointer items-center rounded-lg border border-gray-300 px-4 transition-all duration-200 hover:border-blue-500"
            >
                <input
                    type="radio"
                    v-model="selectedAnswer"
                    :value="answer.id"
                    :id="'answer-' + answer.id"
                    name="answer"
                    class="h-5 w-5 text-blue-600"
                />
                <label
                    :for="'answer-' + answer.id"
                    class="ml-3 w-full cursor-pointer text-gray-700"
                    >{{ answer.name?.toUpperCase() }}</label
                >
            </div>
        </div>
    </div>
</template>
<script setup>
import { ref, watch } from 'vue';

const emits = defineEmits(['sendAnswer']);

const props = defineProps(['question', 'answers']);

const selectedAnswer = ref(null);

// Reiniciar la selección cuando la pregunta cambie
watch(
    () => props.question,
    () => {
        selectedAnswer.value = null;
    },
    { deep: true },
);

watch(selectedAnswer, (value) => {
    emits('sendAnswer', value);
});
</script>
<style scoped>
.answer-option:has(input:checked) {
    background-color: #eff6ff;
    border-color: #3b82f6;
}
.answer-option:has(input:checked) label {
    font-weight: 600;
    color: #1e3a8a;
}

.answer-option:has(input:checked):hover label {
    color: #1e3a8a;
}
</style>
