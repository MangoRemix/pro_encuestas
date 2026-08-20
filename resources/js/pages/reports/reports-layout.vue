<template>
    <Head title="Reportes" />
    <MainLayout>
        <div class="w-1/2 mx-auto">
            <select v-model="selectedSurvey" name="" id="" class="inputs-form bg-white">
                <option :value="0">

                </option>
                <option :value="survey.id" v-for="survey in surveys">
                    {{ survey.name }}
                </option>
            </select>
        </div>            
        <div class="flex items-center justify-around gap-x-3 mt-6 w-1/2 mx-auto text-white ">
            
            <div class="flex space-x-2.5 items-center">
                <label for="">Tabla</label>
                <input type="radio"
                v-model="selected_radio"
                value="table" name="reportType" id="">
            </div>
            
            
            <div class="flex space-x-2.5 items-center">
                <label for="">Gráfica</label>
                <input type="radio"
                v-model="selected_radio"
                value="graphics" name="reportType" id="">
            </div>
            
            
            <div class="flex space-x-2.5 items-center">
                <label for="">Ambos</label>
                <input type="radio"
                v-model="selected_radio"
                value="both" name="reportType" id="">
            </div>
        </div>
        <h2 class="text-xl lg:text-3xl text-white font-bold mt-8 mb-6 underline text-center">{{ survey_selected?.name }} </h2>
       
        <div v-if="survey_selected" class="text-white font-semibold text-center">
            <h4 class="text-xl lg:text-2xl">Categorías</h4>
            <span>Total encuestados: {{ reportData.total_respondent }}</span>
        </div>

        <div class="min-h-20 w-full flex flex-wrap gap-x-3 px-2 justify-center">
            <div class="w-fit">
                <button @click="category_selected = null" class="primary-button-app cursor-pointer">
                    TODAS
                </button>
            </div>
            <div class="w-fit" v-for="category in categories">
                <button @click="category_selected = category.name" class="primary-button-app cursor-pointer">
                    {{ category.name }}
                </button>
            </div>
        </div>

        <div v-if="selected_radio === 'table' || selected_radio === 'both'">
            <Table :categories="filteredCategories" />
        </div>

        <div v-if="selected_radio === 'graphics' || selected_radio === 'both'">
            <Graphics :categories="filteredCategories" />
        </div>
    </MainLayout>
</template>
<script setup>
import { getCategoriesBySurvey, getSurveys } from '@/composables/api/surveys';
import { getReportStructure } from '@/composables/api/reports';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, onMounted, watch, computed } from 'vue';
import MainLayout from '@/layouts/main-layout.vue';
import Graphics from './graphics.vue';
import Table from './table.vue';

const selectedSurvey = ref(null)
const surveys = ref([])
const categories = ref([])
const category_selected = ref()
const survey_selected = ref(null)
const selected_radio = ref('table')
const page = usePage()

const reportData = ref([]);

const filteredCategories = computed(() => {
    if (!category_selected.value) return reportData.value.categories || [];
    return (reportData.value.categories || []).filter(
        c => c.name === category_selected.value
    );
});

onMounted(async () => {
    try {
        
        surveys.value = await allSurveys()
        
    } catch (e) {
        console.error("Error cargando reporte:", e);
    }
});

const allSurveys = async ()=>{
    
    const {data,errorFlag,responseMessage} = await getSurveys({all:true})
    if(data){
        
        selectedSurvey.value = data[0]
        return data
    }else{
        return []
    }
}

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

watch([selectedSurvey], loadReport, { deep: true });
</script>
<style scoped>
    
</style>
