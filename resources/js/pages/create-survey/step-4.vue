<template>
    <MainLayout>
        <StepNavigation :items="steps" :current="current" />
        <div
            class="grow scrollbar-thumb-blue-800 scrollbar-track-white/30 overflow-y-scroll rounded-xl border border-blue-700/50 bg-gray-500/50 px-2 py-6 shadow-lg backdrop-blur-md md:px-6"
        >
            <h2 class="mb-6 text-xl font-bold text-white underline md:text-2xl">
                Resumen de la Encuesta
            </h2>

            <div v-if="fullSurvey" class="space-y-4">
                <div
                    v-for="category in fullSurvey.categories"
                    :key="category.id"
                    class="overflow-hidden rounded-lg border border-blue-800/50 bg-blue-950/40"
                >
                    <button
                        @click="toggleCategory(category.id)"
                        class="flex w-full cursor-pointer items-center justify-between p-4 text-sm font-bold text-white transition-colors hover:bg-blue-800/60"
                    >
                        {{ category.name }}
                        <Icon
                            :icon="
                                openItems.category === category.id
                                    ? 'ic:baseline-expand-less'
                                    : 'ic:baseline-expand-more'
                            "
                            class="text-xl"
                        />
                    </button>

                    <div
                        v-if="openItems.category === category.id"
                        class="border-t border-blue-800/50 bg-blue-900/20 p-3"
                    >
                        <div
                            v-for="question in category.questions"
                            :key="question.id"
                            class="mb-2 overflow-hidden rounded-lg border border-blue-800/30 bg-blue-950/60"
                        >
                            <button
                                @click="toggleQuestion(question.id)"
                                class="flex w-full cursor-pointer items-center justify-between p-3 text-xs text-blue-100 hover:bg-blue-900/50 md:text-sm"
                            >
                                {{ question.name }}
                                <Icon
                                    :icon="
                                        openItems.question === question.id
                                            ? 'ic:baseline-expand-less'
                                            : 'ic:baseline-expand-more'
                                    "
                                    class="text-xs"
                                />
                            </button>

                            <div
                                v-if="openItems.question === question.id"
                                class="bg-blue-950/80 p-4 text-xs text-blue-200 md:text-sm"
                            >
                                <div
                                    v-for="answer in question.answers"
                                    :key="answer.id"
                                    class="border-b border-blue-800/30 p-2 last:border-0 hover:bg-blue-800/40"
                                >
                                    {{ answer.name }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="py-10 text-center text-white">
                <p>Cargando encuesta...</p>
            </div>
        </div>
        <div class="mx-auto mb-3 w-7/12 md:w-1/4">
            <button @click="NextStep()" class="green-button-app cursor-pointer">
                Ver listado de encuestas
            </button>
        </div>
    </MainLayout>
</template>
<script setup>
import { Icon } from '@iconify/vue';
import { router, usePage } from '@inertiajs/vue3';
import { onMounted, reactive, ref } from 'vue';
import StepNavigation from '@/components/StepNavigation.vue';
import { showFullSurvey } from '@/composables/api/surveys';
import MainLayout from '@/layouts/main-layout.vue';
import { currentStep, stepsBreadcrumb } from '@/store/store';

const page = usePage();

const steps = stepsBreadcrumb;
const current = currentStep;

const fullSurvey = ref(null);

const openItems = reactive({
    category: null,
    question: null,
});

const toggleCategory = (id) => {
    openItems.category = openItems.category === id ? null : id;
    openItems.question = null;
};

const toggleQuestion = (id) => {
    openItems.question = openItems.question === id ? null : id;
};

onMounted(async () => {
    current.value = 'Resumen';
    const { data } = await showFullSurvey(page.props.surveyId);
    fullSurvey.value = data;
    console.log(fullSurvey.value);
});

const NextStep = () => {
    router.get('/surveys', {
        surveyId: page.props.surveyId,
    });
};
</script>
<style scoped></style>
