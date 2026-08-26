<template>
    <Head title="Reportes" />
    <MainLayout>
        <div class="w-11/12 mx-auto space-y-6">
            <div class="bg-white p-3 rounded-2xl shadow-sm flex flex-wrap gap-4 items-center">
                <div class="flex-1 min-w-62.5">
                    <select v-model="selectedSurvey" class="w-9/12 rounded-lg border-slate-200 text-slate-700 focus:ring-blue-600 focus:border-indigo-600">
                        <option value="">Seleccione una encuesta</option>
                <option v-for="survey in surveys" :key="survey.id" :value="survey.id">
                    {{ survey.name }}
                </option>
            </select>
        </div>            

                <div class="flex items-center gap-x-4">
                    <label v-for="opt in reportTypes" :key="opt.value" class="flex items-center gap-2 cursor-pointer text-slate-600 font-medium">
                        <input type="radio" v-model="selected_radio" :value="opt.value" name="reportType" class="text-indigo-600 focus:ring-indigo-500">
                        {{ opt.label }}
            </label>
        </div>
        <div class="flex items-center gap-x-2">
            <span class="text-sm font-semibold opacity-75">Tipo:</span>
            <select v-model="selected_graphic" class="border-none focus:ring-0 text-slate-700">
                <option v-for="option in graphicOptions" :key="option.component" :value="option.component">
                    {{ option.name }}
                </option>
            </select>
        </div>
        </div>
        </div>
        <!-- <h2 class="text-2xl lg:text-4xl text-white font-extrabold mt-8 mb-6 text-center">{{ survey_selected?.name }} </h2> -->
       
        <!-- Dropdown de selección de gráficas -->
        <div class="w-fit flex items-center gap-x-3 max-w-2xl ml-13 my-6 bg-white p-4 rounded-xl shadow-sm">
            <span class="text-sm font-semibold opacity-75">Total encuestados: {{ reportData.total_respondent }}</span>
            
        </div>
        
        <div class="w-11/12 mx-auto flex justify-center" v-if="selected_graphic=='graphics'">
            <CategoryFilter
                :categories="categories"
                v-model="category_selected"
            />
        </div>

        <div v-if="['table', 'both'].includes(selected_radio)" class="">
            <Transition name="fade" mode="out-in">
                <div class="text-slate-800">
            <Table :categories="filteredCategories" />
                </div>
            </Transition>
        </div>

        <div v-if="['graphics', 'both'].includes(selected_radio)" class="mt-6 space-y-6">
            <Transition name="fade" mode="out-in">
            <template v-if="['all', 'graphics'].includes(selected_graphic)">
            <Graphics :categories="filteredCategories" :total-respondent="reportData.total_respondent" />
</template>
            </Transition>

            <div v-if="survey_selected" class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <template v-if="['all', 'sexchart'].includes(selected_graphic)">
                    <SexChart :survey-id="survey_selected.id" :total-respondent="reportData.total_respondent" />
                </template>
                <template v-if="['all', 'parishchart'].includes(selected_graphic)">
                    <ParishChart :survey-id="survey_selected.id" :total-respondent="reportData.total_respondent" />
                </template>
            </div>

            <template v-if="['all', 'agerangechart'].includes(selected_graphic) && survey_selected && reportData.total_respondent">
                <AgeRangeFilter :survey-id="survey_selected.id" :total-respondent="reportData.total_respondent"/>
            </template>
        </div>
    </MainLayout>
</template>

<script setup>

import { getCategoriesBySurvey, getSurveys } from '@/composables/api/surveys';
import { getReportStructure } from '@/composables/api/reports';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted, watch, computed } from 'vue';
import MainLayout from '@/layouts/main-layout.vue';
import Graphics from './sublayouts/graphics.vue';
import Table from './sublayouts/table.vue';
import AgeRangeFilter from './sublayouts/AgeRangeFilter.vue';
import SexChart from './sublayouts/SexChart.vue';
import ParishChart from './sublayouts/ParishChart.vue';
import CategoryFilter from '@/components/CategoryFilter.vue';

const selectedSurvey = ref(null)
const surveys = ref([])
const categories = ref([])
const category_selected = ref(null)
const survey_selected = ref(null)
const selected_radio = ref('table')
const page = usePage()

const reportData = ref([]);

const reportTypes = [
    { label: 'Tabla', value: 'table' },
    { label: 'Gráfica', value: 'graphics' },
    { label: 'Ambos', value: 'both' }
];

const filteredCategories = computed(() => {
    if (!category_selected.value) return reportData.value.categories || [];
    return (reportData.value.categories || []).filter(
        c => c.name === category_selected.value
    );
});

const graphicOptions = [
    { name: 'Todas las gráficas', component: 'all' },
    { name: 'Categorías', component: 'graphics' },
    { name: 'Sexo', component: 'sexchart' },
    { name: 'Parroquias', component: 'parishchart' },
    { name: 'Rangos de edad', component: 'agerangechart' }
];

const selected_graphic = ref('all')


onMounted(async () => {
    try {
        const { data } = await getSurveys({ all: true });
        if (data?.length) {
            surveys.value = data;
            //selectedSurvey.value = data[0].id;
        }
    } catch (e) {
        console.error("Error cargando reporte:", e);
    }
});

const loadReport = async () => {
    if (!selectedSurvey.value) return;

    survey_selected.value = surveys.value.find((s) => s.id == selectedSurvey.value)
    if (survey_selected.value) {
        const { data } = await getCategoriesBySurvey(selectedSurvey.value)
        if (data) categories.value = data;

        const report = await getReportStructure(selectedSurvey.value);
        if (report.data) reportData.value = report.data;
    }
};

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

