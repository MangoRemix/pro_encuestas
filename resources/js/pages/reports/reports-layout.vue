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
                class="mt-3 flex flex-wrap items-end gap-4 border-t border-slate-200 pt-3"
            >
                <div class="min-w-64 flex-1">
                    <label
                        class="mb-1 block text-sm font-semibold text-slate-600"
                    >
                        Actividad:
                    </label>
                    <select
                        v-model="selectedActivity"
                        class="w-full rounded-lg border-slate-300 bg-white text-gray-900 focus:border-indigo-600 focus:ring-blue-600"
                    >
                        <option value="">Todas las actividades</option>
                        <option
                            v-for="activity in activities"
                            :key="activity.id"
                            :value="activity.id"
                        >
                            {{
                                (activity.parishes || [])
                                    .map((parish) => parish.name)
                                    .join(', ')
                            }}
                            ({{ formatedDate(activity.init_date) }} -
                            {{ formatedDate(activity.finish_date) }})
                        </option>
                    </select>
                </div>

                <div class="min-w-48 flex-1">
                    <label
                        class="mb-1 block text-sm font-semibold text-slate-600"
                    >
                        Parroquia:
                    </label>
                    <select
                        v-model="selectedParish"
                        class="w-full rounded-lg border-slate-300 bg-white text-gray-900 focus:border-indigo-600 focus:ring-blue-600"
                    >
                        <option value="">Todas las parroquias</option>
                        <option
                            v-for="parish in parishes"
                            :key="parish.id"
                            :value="parish.id"
                        >
                            {{ parish.name }}
                        </option>
                    </select>
                </div>

                <div class="min-w-48 flex-1">
                    <label
                        class="mb-1 block text-sm font-semibold text-slate-600"
                    >
                        Encuestador:
                    </label>
                    <select
                        v-model="selectedPollster"
                        class="w-full rounded-lg border-slate-300 bg-white text-gray-900 focus:border-indigo-600 focus:ring-blue-600"
                    >
                        <option value="">Todos los encuestadores</option>
                        <option
                            v-for="pollster in pollsters"
                            :key="pollster.id"
                            :value="pollster.id"
                        >
                            {{ pollster.name }}
                        </option>
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-semibold text-slate-600"
                        >Desde:</label
                    >
                    <input
                        type="date"
                        v-model="dateFrom"
                        :max="dateTo || undefined"
                        class="rounded-lg border-slate-300 bg-white text-gray-900 focus:border-indigo-600 focus:ring-blue-600"
                    />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-semibold text-slate-600"
                        >Hasta:</label
                    >
                    <input
                        type="date"
                        v-model="dateTo"
                        :min="dateFrom || undefined"
                        class="rounded-lg border-slate-300 bg-white text-gray-900 focus:border-indigo-600 focus:ring-blue-600"
                    />
                </div>
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

        <template v-else-if="filtersUnrelated">
            <div class="mx-auto w-11/12">
                <p
                    class="mt-10 rounded-xl border border-dashed border-amber-400 bg-amber-50 py-14 text-center text-lg font-semibold text-amber-700"
                >
                    No se encontraron resultados: la parroquia o el encuestador
                    elegidos no tienen relación con la actividad seleccionada.
                </p>
            </div>
        </template>

        <template v-else>
            <!-- Dropdown de selección de gráficas -->
            <div
                class="my-6 ml-13 flex w-fit max-w-2xl items-center gap-x-3 rounded-xl bg-white p-4 shadow-sm"
            >
                <span class="text-sm font-semibold opacity-75"
                    >Total encuestados: {{ reportData.total_respondent }}</span
                >
            </div>

            <!-- Cuántas encuestas subió cada encuestador en la actividad
                 elegida — solo tiene sentido cuando hay una actividad
                 puntual seleccionada (no "todas las actividades"). -->
            <div
                v-if="selectedActivity && pollsterCounts.length > 0"
                class="mx-auto mb-6 w-11/12 overflow-hidden rounded-xl bg-white shadow-sm"
            >
                <h3 class="p-4 pb-2 text-sm font-semibold text-slate-600">
                    Encuestas subidas por encuestador en esta actividad
                </h3>
                <table class="w-full border-collapse text-left text-slate-800">
                    <thead>
                        <tr
                            class="border-b border-slate-200 text-xs text-slate-500 uppercase"
                        >
                            <th class="p-3">Encuestador</th>
                            <th class="p-3 text-right">Encuestas subidas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="row in pollsterCounts"
                            :key="row.pollster_id"
                        >
                            <td class="p-3">{{ row.pollster_name }}</td>
                            <td class="p-3 text-right font-semibold">
                                {{ row.total_surveys_conducted }}
                            </td>
                        </tr>
                    </tbody>
                </table>
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
                            :parish-id="selectedParish"
                            :pollster-id="selectedPollster"
                            :date-from="dateFrom"
                            :date-to="dateTo"
                        />
                    </template>
                    <template
                        v-if="['all', 'parishchart'].includes(selected_graphic)"
                    >
                        <ParishChart
                            :survey-id="survey_selected.id"
                            :total-respondent="reportData.total_respondent"
                            :activity-id="selectedActivity"
                            :parish-id="selectedParish"
                            :pollster-id="selectedPollster"
                            :date-from="dateFrom"
                            :date-to="dateTo"
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
                        :parish-id="selectedParish"
                        :pollster-id="selectedPollster"
                        :date-from="dateFrom"
                        :date-to="dateTo"
                    />
                </template>
            </div>
        </template>
    </MainLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, onMounted, watch, computed } from 'vue';
import CategoryFilter from '@/components/CategoryFilter.vue';
import { useParishes } from '@/composables/api/parishes';
import {
    getActivitiesForSurveyReport,
    getPollsterCountsForActivity,
    getReportStructure,
} from '@/composables/api/reports';
import { getCategoriesBySurvey, getSurveys } from '@/composables/api/surveys';
import { formatedDate } from '@/composables/shared.js';
import MainLayout from '@/layouts/main-layout.vue';
import { apiHost } from '@/store/store';
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

const { parishes, fetchParishes } = useParishes();
const pollsters = ref([]);
const selectedParish = ref('');
const selectedPollster = ref('');
const dateFrom = ref('');
const dateTo = ref('');

const pollsterCounts = ref([]);

const reportData = ref([]);
const filtersUnrelated = ref(false);

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
    await fetchParishes();

    try {
        const { data } = await getSurveys({ all: true });

        if (data?.length) {
            surveys.value = data;
            //selectedSurvey.value = data[0].id;
        }
    } catch (e) {
        console.error('Error cargando reporte:', e);
    }

    try {
        const { data } = await axios.get(
            `${apiHost}person/pollster-admin/list`,
            {
                params: { per_page: 1000 },
            },
        );
        pollsters.value = (data?.data || []).filter(
            (person) => person.rol?.name === 'POLLSTER',
        );
    } catch (e) {
        console.error('Error cargando encuestadores:', e);
    }
});

const currentFilters = () => ({
    activityId: selectedActivity.value,
    parishId: selectedParish.value,
    pollsterId: selectedPollster.value,
    dateFrom: dateFrom.value,
    dateTo: dateTo.value,
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

        const report = await getReportStructure(
            selectedSurvey.value,
            currentFilters(),
        );

        if (report.noRelation) {
            filtersUnrelated.value = true;
            reportData.value = [];

            return;
        }

        filtersUnrelated.value = false;

        if (report.data) {
            reportData.value = report.data;
        }
    }
};

const loadPollsterCounts = async () => {
    if (!selectedActivity.value) {
        pollsterCounts.value = [];

        return;
    }

    const { data } = await getPollsterCountsForActivity(selectedActivity.value);
    pollsterCounts.value = data || [];
};

watch(selectedSurvey, async (surveyId) => {
    selectedActivity.value = '';
    selectedParish.value = '';
    selectedPollster.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    activities.value = [];

    if (!surveyId) {
        return;
    }

    const { data } = await getActivitiesForSurveyReport(surveyId);

    if (data) {
        activities.value = data;
    }
});

watch(selectedActivity, () => {
    loadReport();
    loadPollsterCounts();
});

watch([selectedParish, selectedPollster, dateFrom, dateTo], loadReport);

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
