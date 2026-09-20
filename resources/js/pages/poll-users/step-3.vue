<template>
    <Head title="Encuestado en curso" />
    <div
        id="background-poll"
        class="flex min-h-screen items-center gap-y-3 px-5 dark:bg-gray-800"
    >
        <SuccessModal :show="showSuccess" />
        <div
            class="mx-auto flex h-[95vh] w-full max-w-7xl flex-col justify-between overflow-hidden rounded-3xl bg-white md:w-10/12"
        >
            <!-- Barra de Progreso -->
            <div class="mb-2 w-full shrink-0 px-8 pt-6">
                <div class="mb-1 flex justify-between text-sm text-gray-500">
                    <span>Progreso</span>
                    <span
                        >{{ currentQuestionIndex }} /
                        {{ totalQuestionsCount }}</span
                    >
                </div>
                <div class="h-2.5 w-full rounded-full bg-gray-200">
                    <div
                        class="h-2.5 rounded-full bg-blue-900 transition-all duration-500"
                        :style="{
                            width: `${(currentQuestionIndex / totalQuestionsCount) * 100}%`,
                        }"
                    ></div>
                </div>
            </div>

            <!-- Questions and Answer Component -->
            <h1
                class="mb-4 flex h-20 w-full shrink-0 items-center justify-center bg-blue-900 text-lg font-bold text-white md:text-2xl"
            >
                {{ c?.name.toUpperCase() }}
            </h1>
            <!-- Questions and Answer Component -->
            <div class="min-w-fit grow md:px-2 lg:max-h-66">
                <QuestionsAndAnswer
                    :key="q.id"
                    @send-answer="getAnswer"
                    class="w-full"
                    v-if="q"
                    :question="q"
                    :answers="a"
                />
            </div>

            <!-- Centered Buttons -->
            <div
                class="mt-auto flex w-full shrink-0 justify-around space-x-3 px-8 py-6 md:space-x-0"
            >
                <button
                    :disabled="disabledRewind"
                    type="button"
                    class="yellow-button-app basis-xs cursor-pointer rounded px-4 py-2 text-white"
                    @click="decrementQuestion"
                >
                    Anterior
                </button>
                <button
                    v-if="!visibilityFinishButton"
                    :disabled="!disabledRewind && selectedAnswer.length === 0"
                    type="button"
                    class="primary-button-app basis-xs cursor-pointer rounded px-4 py-2 text-white"
                    @click="incrementQuestion"
                >
                    Siguiente
                </button>

                <button
                    v-if="visibilityFinishButton"
                    type="button"
                    class="green-button-app basis-xs cursor-pointer rounded px-4 py-2 text-white"
                    @click="finishSurvey"
                    :disabled="selectedAnswer.length === 0"
                >
                    Finalizar
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref, watch, computed } from 'vue';
import QuestionsAndAnswer from '@/components/poll/QuestionsAndAnswer.vue';
import SuccessModal from '@/components/SuccessModal.vue';

const page = usePage();

const survey = ref(null);
const showSuccess = ref(false);

const q = ref([]);
const a = ref([]);
const c = ref();
// Siempre un arreglo: 0 o 1 elemento para preguntas de una sola respuesta,
// varios para preguntas de selección múltiple.
const selectedAnswer = ref([]);
const counts = ref({
    actual_category: 0,
    actual_question: 0,
    total_categories: 0,
    total_questions: 0,
});

const disabledRewind = ref(true);

const visibilityFinishButton = ref(false);

const totalQuestionsCount = computed(() => {
    if (!survey.value) {
        return 0;
    }

    return survey.value.categories.reduce(
        (acc, cat) => acc + (cat.questions?.length || 0),
        0,
    );
});

const currentQuestionIndex = computed(() => {
    if (!survey.value) {
        return 0;
    }

    let index = 0;

    for (let i = 0; i < counts.value.actual_category; i++) {
        index += survey.value.categories[i].questions?.length || 0;
    }

    return index + counts.value.actual_question + 1;
});

onMounted(async () => {
    try {
        const response = await axios.get(
            `/api/survey/show-full/${page.props.id}`,
        );

        survey.value = response.data;
    } catch (error) {
        console.error('Error cargando la encuesta completa:', error);
    }
});
watch(survey, async (value) => {
    if (value.categories.length > 0) {
        counts.value.total_categories = value.categories.length;

        const { questions, ...category } = value.categories[0];

        if (questions && questions.length > 0) {
            const { answers, ...rest } = questions[0];

            counts.value.total_questions = questions.length;

            q.value = rest;
            a.value = answers;
            c.value = category;
        }
    }
});

watch(q, (value) => {
    router.get(
        `/poll-users/step-3/${page.props.userId}/survey/${page.props.id}`,
        {
            category: c.value.id,
            question: value?.id,
        },
        { preserveState: true, replace: true },
    );
});

const incrementQuestion = async () => {
    try {
        await storageResults();
        selectedAnswer.value = [];

        const currentCat =
            survey.value.categories[counts.value.actual_category];
        const totalQuestionsInCat = currentCat.questions.length;

        if (counts.value.actual_question < totalQuestionsInCat - 1) {
            // Hay más preguntas en la categoría actual
            counts.value.actual_question++;
        } else if (
            counts.value.actual_category <
            survey.value.categories.length - 1
        ) {
            // Se acabó la categoría actual, pasar a la siguiente
            counts.value.actual_category++;
            counts.value.actual_question = 0;
        }

        // Actualizar datos de la pregunta actual
        const nextCat = survey.value.categories[counts.value.actual_category];
        const nextQ = nextCat.questions[counts.value.actual_question];

        c.value = { ...nextCat, questions: undefined }; // Evitar pasar todo el array
        q.value = { ...nextQ, answers: undefined };
        a.value = nextQ.answers;

        disabledRewind.value = false;

        // Verificar si es la última pregunta de la última categoría
        const isLastCategory =
            counts.value.actual_category === survey.value.categories.length - 1;
        const isLastQuestion =
            counts.value.actual_question ===
            survey.value.categories[counts.value.actual_category].questions
                .length -
                1;

        visibilityFinishButton.value = isLastCategory && isLastQuestion;
    } catch (error) {
        console.error(error);
    }
};

const decrementQuestion = () => {
    try {
        if (counts.value.actual_question > 0) {
            counts.value.actual_question--;
        } else if (counts.value.actual_category > 0) {
            counts.value.actual_category--;
            counts.value.actual_question =
                survey.value.categories[counts.value.actual_category].questions
                    .length - 1;
        }

        const prevCat = survey.value.categories[counts.value.actual_category];
        const prevQ = prevCat.questions[counts.value.actual_question];

        c.value = { ...prevCat, questions: undefined };
        q.value = { ...prevQ, answers: undefined };
        a.value = prevQ.answers;
        disabledRewind.value =
            counts.value.actual_category === 0 &&
            counts.value.actual_question === 0;
        visibilityFinishButton.value = false;
    } catch (error) {
        console.error(error);
    }
};

const storageResults = () => {
    if (selectedAnswer.value.length === 0) {
        return;
    }

    const historial = JSON.parse(localStorage.getItem('miHistorialData')) || [];
    const questionId = parseInt(page.props.question);
    const personId = parseInt(page.props.userId);
    const pollsterId = page.props.auth.user.id;

    // Quita cualquier respuesta previa para esta pregunta (si el encuestador
    // retrocedió y cambió su selección) y guarda una fila por cada respuesta
    // marcada — una sola para selección única, varias para selección múltiple.
    const withoutThisQuestion = historial.filter(
        (item) => item.question_id !== questionId,
    );

    const entriesForThisQuestion = selectedAnswer.value.map((answerId) => ({
        person_id: personId,
        question_id: questionId,
        answer_id: answerId,
        pollster_id: pollsterId,
    }));

    localStorage.setItem(
        'miHistorialData',
        JSON.stringify([...withoutThisQuestion, ...entriesForThisQuestion]),
    );
};

const finishSurvey = async () => {
    await storageResults();

    const historial = JSON.parse(localStorage.getItem('miHistorialData')) || [];

    if (historial.length === 0) {
        return;
    }

    const allSurveys =
        JSON.parse(localStorage.getItem('allSurveysPending')) || [];
    const surveyToSave = {
        data: historial,
        status: 'PENDIENTE',
        survey: survey.value,
        created_at: new Date().toISOString(),
    };
    allSurveys.push(surveyToSave);
    localStorage.setItem('allSurveysPending', JSON.stringify(allSurveys));

    try {
        // Las respuestas quedan encoladas en 'allSurveysPending' (localStorage) y se
        // sincronizan con el backend desde /poll-users/finished-list ("Guardar Todas"),
        // no aquí — así el encuestador puede seguir aplicando encuestas sin conexión.
        localStorage.removeItem('miHistorialData');

        showSuccess.value = true;
        setTimeout(() => {
            showSuccess.value = false;
            router.visit('/poll-users/finished-list');
        }, 2000);
    } catch (error) {
        console.error('Error al finalizar la encuesta:', error);
    }
};

const getAnswer = (answerIds) => {
    selectedAnswer.value = answerIds;
};
</script>

<style scoped>
/* You can add your custom CSS here if needed */
</style>
