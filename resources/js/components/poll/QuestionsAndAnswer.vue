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
                @click="toggleAnswer(answer.id)"
                class="answer-option flex min-h-10 w-full cursor-pointer items-center rounded-lg border border-gray-300 px-4 transition-all duration-200 hover:border-blue-500"
            >
                <input
                    v-if="question.allows_multiple_answers"
                    type="checkbox"
                    :checked="selectedAnswers.includes(answer.id)"
                    :id="'answer-' + answer.id"
                    class="h-5 w-5 text-blue-600"
                    @click.stop="toggleAnswer(answer.id)"
                />
                <input
                    v-else
                    type="radio"
                    v-model="selectedAnswers[0]"
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

// Siempre un arreglo: selección única guarda como máximo un id, selección
// múltiple puede guardar varios. El componente padre recibe siempre un
// arreglo de ids seleccionados.
const selectedAnswers = ref([]);

// Reiniciar la selección cuando la pregunta cambie
watch(
    () => props.question,
    () => {
        selectedAnswers.value = [];
    },
    { deep: true },
);

const toggleAnswer = (answerId) => {
    if (props.question.allows_multiple_answers) {
        const index = selectedAnswers.value.indexOf(answerId);

        if (index === -1) {
            selectedAnswers.value.push(answerId);
        } else {
            selectedAnswers.value.splice(index, 1);
        }
    } else {
        selectedAnswers.value = [answerId];
    }
};

watch(
    selectedAnswers,
    (value) => {
        emits('sendAnswer', [...value]);
    },
    { deep: true },
);
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
