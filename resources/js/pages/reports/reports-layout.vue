<template>
    <Head title="Reportes" />
    <MainLayout>
        <div class="mx-auto w-11/12 space-y-6">
            <div
                class="flex flex-wrap items-center gap-4 rounded-2xl bg-white p-3 shadow-sm"
            >
                <div class="min-w-62.5 flex-1">
                    <label
                        for="report-survey-select"
                        class="mb-1 block text-sm font-semibold text-slate-600"
                    >
                        Selecciona una encuesta:
                    </label>
                    <select
                        id="report-survey-select"
                        v-model="selectedSurvey"
                        class="w-9/12 rounded-lg border-slate-200 text-slate-700 focus:border-indigo-600 focus:ring-blue-600"
                    >
                        <option value="">Seleccione una encuesta</option>
                        <option
                            v-for="survey in surveys"
                            :key="survey.id"
                            :value="survey.id"
                        >
                            {{ survey.name }}
                        </option>
                    </select>
                </div>

                <div class="flex items-center gap-x-4">
                    <label
                        v-for="opt in reportTypes"
                        :key="opt.value"
                        class="flex cursor-pointer items-center gap-2 font-medium text-slate-600"
                    >
                        <input
                            type="radio"
                            v-model="selected_radio"
                            :value="opt.value"
                            name="reportType"
                            class="text-indigo-600 focus:ring-indigo-500"
                        />
                        {{ opt.label }}
                    </label>
                </div>
                <div class="flex items-center gap-x-2">
                    <span class="text-sm font-semibold opacity-75">Tipo:</span>
                    <select
                        v-model="selected_graphic"
                        class="border-none text-slate-700 focus:ring-0"
                    >
                        <option
                            v-for="option in graphicOptions"
                            :key="option.component"
                            :value="option.component"
                        >
                            {{ option.name }}
                        </option>
                    </select>
                </div>
            </div>

            <div
                v-if="selectedSurvey"
                class="mt-3 border-t border-slate-200 pt-3"
            >
                <label class="mb-1 block text-sm font-semibold text-slate-600">
                    Actividad:
                </label>
                <select
                    v-model="selectedActivity"
                    class="w-full max-w-md rounded-lg border-slate-300 bg-white text-gray-900 focus:border-indigo-600 focus:ring-blue-600"
                >
                    <option value="">Todas las actividades</option>
                    <option
                        v-for="activity in activities"
                        :key="activity.id"
                        :value="activity.id"
                    >
                        {{ activity.parish?.name }} ({{
                            formatedDate(activity.init_date)
                        }}
                        - {{ formatedDate(activity.finish_date) }})
                    </option>
                </select>
            </div>
        </div>
        <!-- <h2 class="text-2xl lg:text-4xl text-white font-extrabold mt-8 mb-6 text-center">{{ survey_selected?.name }} </h2> -->

        <!-- Estado vacío: aún no se ha elegido una encuesta -->
        <div v-if="!selectedSurvey" class="mx-auto w-11/12">
            <p
                class="mt-10 rounded-xl border border-dashed border-slate-300 bg-white/60 py-14 text-center text-lg font-semibold text-slate-500"
            >
                Selecciona una encuesta arriba para ver sus estadísticas
            </p>
        </div>

        <template v-else>
            <!-- Dropdown de selección de gráficas -->
            <div
                class="my-6 ml-13 flex w-fit max-w-2xl items-center gap-x-3 rounded-xl bg-white p-4 shadow-sm"
            >
                <span class="text-sm font-semibold opacity-75"
                    >Total encuestados: {{ reportData.total_respondent }}</span
                >
            </div>

            <div
                class="mx-auto flex w-11/12 justify-center"
                v-if="selected_graphic == 'graphics'"
            >
                <CategoryFilter
                    :categories="categories"
                    v-model="category_selected"
                />
            </div>

            <Transition name="fade" mode="out-in">
                <div
                    v-if="['table', 'both'].includes(selected_radio)"
                    class="text-slate-800"
                >
                    <Table :categories="filteredCategories" />
                </div>
            </Transition>

            <div
                v-if="['graphics', 'both'].includes(selected_radio)"
                class="mt-6 space-y-6"
            >
                <Transition name="fade" mode="out-in">
                    <template
                        v-if="['all', 'graphics'].includes(selected_graphic)"
                    >
                        <Graphics
                            :categories="filteredCategories"
                            :total-respondent="reportData.total_respondent"
                        />
                    </template>
                </Transition>

                <div
                    v-if="survey_selected"
                    class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-2"
                >
                    <template
                        v-if="['all', 'sexchart'].includes(selected_graphic)"
                    >
                        <SexChart
                            :survey-id="survey_selected.id"
                            :total-respondent="reportData.total_respondent"
                            :activity-id="selectedActivity"
                        />
                    </template>
                    <template
                        v-if="['all', 'parishchart'].includes(selected_graphic)"
                    >
                        <ParishChart
                            :survey-id="survey_selected.id"
                            :total-respondent="reportData.total_respondent"
                            :activity-id="selectedActivity"
                        />
                    </template>
                </div>

                <template
                    v-if="
                        ['all', 'agerangechart'].includes(selected_graphic) &&
                        survey_selected &&
                        reportData.total_respondent
                    "
                >
                    <AgeRangeFilter
                        :survey-id="survey_selected.id"
                        :total-respondent="reportData.total_respondent"
                        :activity-id="selectedActivity"
                    />
                </template>
            </div>
        </template>
    </MainLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, watch, computed } from 'vue';
import CategoryFilter from '@/components/CategoryFilter.vue';
import {
    getActivitiesForSurveyReport,
    getReportStructure,
} from '@/composables/api/reports';
import { getCategoriesBySurvey, getSurveys } from '@/composables/api/surveys';
import { formatedDate } from '@/composables/shared.js';
import MainLayout from '@/layouts/main-layout.vue';
import AgeRangeFilter from './sublayouts/AgeRangeFilter.vue';
import Graphics from './sublayouts/graphics.vue';
import ParishChart from './sublayouts/ParishChart.vue';
import SexChart from './sublayouts/SexChart.vue';
import Table from './sublayouts/table.vue';

const selectedSurvey = ref(null);
const surveys = ref([]);
const categories = ref([]);
const category_selected = ref(null);
const survey_selected = ref(null);
const selected_radio = ref('table');

const activities = ref([]);
const selectedActivity = ref('');

const reportData = ref([]);

const reportTypes = [
    { label: 'Tabla', value: 'table' },
    { label: 'Gráfica', value: 'graphics' },
    { label: 'Ambos', value: 'both' },
];

const filteredCategories = computed(() => {
    if (!category_selected.value) {
        return reportData.value.categories || [];
    }

    return (reportData.value.categories || []).filter(
        (c) => c.name === category_selected.value,
    );
});

const graphicOptions = [
    { name: 'Todas las gráficas', component: 'all' },
    { name: 'Categorías', component: 'graphics' },
    { name: 'Género', component: 'sexchart' },
    { name: 'Parroquias', component: 'parishchart' },
    { name: 'Rangos de edad', component: 'agerangechart' },
];

const selected_graphic = ref('all');

onMounted(async () => {
    try {
        const { data } = await getSurveys({ all: true });

        if (data?.length) {
            surveys.value = data;
            //selectedSurvey.value = data[0].id;
        }
    } catch (e) {
        console.error('Error cargando reporte:', e);
    }
});

const loadReport = async () => {
    if (!selectedSurvey.value) {
        return;
    }

    survey_selected.value = surveys.value.find(
        (s) => s.id == selectedSurvey.value,
    );

    if (survey_selected.value) {
        const { data } = await getCategoriesBySurvey(selectedSurvey.value);

        if (data) {
            categories.value = data;
        }

        const report = await getReportStructure(selectedSurvey.value, {
            activityId: selectedActivity.value,
        });

        if (report.data) {
            reportData.value = report.data;
        }
    }
};

watch(selectedSurvey, async (surveyId) => {
    selectedActivity.value = '';
    activities.value = [];

    if (!surveyId) {
        return;
    }

    const { data } = await getActivitiesForSurveyReport(surveyId);

    if (data) {
        activities.value = data;
    }
});

watch(selectedActivity, loadReport);

watch(selectedSurvey, loadReport, { immediate: true });
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
