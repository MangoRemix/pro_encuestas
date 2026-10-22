<template>
    <MainLayout>
        <StepNavigation :items="steps" :current="current" />
        <div
            class="grow overflow-y-auto rounded-xl border border-slate-200 bg-white px-4 py-6 shadow-md md:px-8"
        >
            <h2 class="mb-6 text-xl font-extrabold text-slate-900 md:text-2xl">
                Resumen de la Encuesta
            </h2>

            <div v-if="fullSurvey" class="space-y-4">
                <div
                    v-for="category in fullSurvey.categories"
                    :key="category.id"
                    class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"
                >
                    <button
                        @click="toggleCategory(category.id)"
                        class="flex w-full cursor-pointer items-center justify-between bg-slate-900 p-4 text-sm font-bold text-white transition-colors hover:bg-slate-800"
                    >
                        <span>{{ category.name }}</span>
                        <Icon
                            :icon="
                                openItems.category === category.id
                                    ? 'ic:baseline-expand-less'
                                    : 'ic:baseline-expand-more'
                            "
                            class="text-xl text-slate-300"
                        />
                    </button>

                    <div
                        v-if="openItems.category === category.id"
                        class="border-t border-slate-200 bg-slate-50 p-4 space-y-3"
                    >
                        <div
                            v-for="question in category.questions"
                            :key="question.id"
                            class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"
                        >
                            <button
                                @click="toggleQuestion(question.id)"
                                class="flex w-full cursor-pointer items-center justify-between p-3.5 text-xs font-semibold text-slate-800 transition-colors hover:bg-slate-100 md:text-sm"
                            >
                                <span>{{ question.name }}</span>
                                <Icon
                                    :icon="
                                        openItems.question === question.id
                                            ? 'ic:baseline-expand-less'
                                            : 'ic:baseline-expand-more'
                                    "
                                    class="text-base text-slate-500"
                                />
                            </button>

                            <div
                                v-if="openItems.question === question.id"
                                class="border-t border-slate-200 bg-slate-50/70 p-3 text-xs text-slate-700 md:text-sm"
                            >
                                <div
                                    v-for="answer in question.answers"
                                    :key="answer.id"
                                    class="border-b border-slate-200/60 p-2.5 last:border-0 hover:bg-slate-100/80 transition-colors rounded"
                                >
                                    {{ answer.name }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="py-10 text-center text-slate-500 font-medium">
                <p>Cargando encuesta...</p>
            </div>
        </div>
        <div class="mx-auto my-4 w-7/12 md:w-1/4">
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
