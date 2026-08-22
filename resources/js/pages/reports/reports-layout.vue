<template>
    <Head title="Reportes" />
    <MainLayout>
        <div class="w-1/2 mx-auto space-y-6">
            <select v-model="selectedSurvey" class="inputs-form bg-white w-full">
                <option value=""></option>
                <option v-for="survey in surveys" :key="survey.id" :value="survey.id">
                    {{ survey.name }}
                </option>
            </select>
        </div>            
        <div class="flex items-center justify-around gap-x-3 mt-6 w-1/2 mx-auto text-white ">
            <label v-for="opt in reportTypes" :key="opt.value" class="flex space-x-2.5 items-center cursor-pointer">
                <input type="radio" v-model="selected_radio" :value="opt.value" name="reportType">
                <span>{{ opt.label }}</span>
            </label>
        </div>
        <h2 class="text-xl lg:text-3xl text-white font-bold mt-8 mb-6 underline text-center">{{ survey_selected?.name }} </h2>
       
        <div v-if="survey_selected" class="text-white font-semibold text-center">
            <h4 class="text-xl lg:text-2xl">Categorías</h4>
            <span>Total encuestados: {{ reportData.total_respondent }}</span>
        </div>

        <!-- Dropdown de selección de gráficas -->
        <div class="w-1/2 mx-auto my-6">
            <select v-model="selected_graphic" class="inputs-form bg-white w-full">
                <option v-for="option in graphicOptions" :key="option.component" :value="option.component">
                    {{ option.name }}
                </option>
            </select>
        </div>
        <div class="min-h-20 w-full flex flex-wrap items-center gap-x-3 px-2 justify-center">
                <div class="w-20">
                    <button @click="category_selected = null" class="primary-button-app cursor-pointer">
                    TODAS
                </button>
                </div>
                <div class="min-w-fit" v-for="category in categories" :key="category.id">
                    <button @click="category_selected = category.name" class="primary-button-app cursor-pointer">
                        {{ category.name }}
                    </button>
                </div>
            </div>

        <div v-if="['table', 'both'].includes(selected_radio)">
            <Table :categories="filteredCategories" />
        </div>

        <div v-if="['graphics', 'both'].includes(selected_radio)">
            <template v-if="['all', 'graphics'].includes(selected_graphic)">
            <Graphics :categories="filteredCategories" :total-respondent="reportData.total_respondent" />
</template>

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