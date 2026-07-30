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
                value="table" name="reportType" checked id="">
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
        <h2 class="text-3xl text-white font-bold mt-8 mb-6 underline text-center">{{ survey_selected?.name }} </h2>
        <Table v-if="selected_radio=='both' || selected_radio=='table'?true:false" :report-data="reportData"/>
        <Graphics v-if="selected_radio=='both' || selected_radio=='graphics'?true:false" :report-data="reportData"/>
    </MainLayout>
</template>
<script setup>
import { getSurveys } from '@/composables/api/surveys';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, onMounted, watch } from 'vue';
import MainLayout from '@/layouts/main-layout.vue';
import Table from './table.vue';
import Graphics from './graphics.vue';

const selectedSurvey = ref(null)
const surveys = ref([])
const survey_selected = ref(null)
const selected_radio = ref('table')
const page = usePage()

const reportData = ref([]);

onMounted(async () => {
    
    try {
        surveys.value = await allSurveys()
        
    } catch (e) {
        console.error("Error cargando reporte:", e);
    }
});

const allSurveys = async ()=>{
    const {data,errorFlag,responseMessage} = await getSurveys({})
    if(data){
        selectedSurvey.value = data.data[0]
        
        return data.data
    }else{
        return []
    }
}

watch(selectedSurvey,async (value)=>{
    
    survey_selected.value = surveys.value.find((s)=>s.id == value)
    //console.log(survey_selected.value)
    if(survey_selected.value?.id){
        const { data } = await axios.get(`/api/result/report/${survey_selected.value.id}`, {
                params: { category_id: page?.props?.categoryId }
            });
        reportData.value = data;
    }else{
        reportData.value = []
    }
    
})
</script>
<style scoped>
    
</style>