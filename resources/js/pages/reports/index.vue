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
        
        <div class="p-6">
            <h2 class="text-3xl text-white font-bold mt-8 mb-6 underline">{{ survey_selected?.name }} </h2>

            <div v-if="reportData.length" class="bg-white/30 backdrop-blur-md shadow-lg rounded-xl p-6 border border-blue-700/50 
                w-full h-135">
                
                <div id="table-header" class="h-10 w-full mb-3">
                    <table class="table-fixed w-full text-left">
                        <thead>
                            <tr class="border-b border-white/30 text-white text-lg">
                                <th class="text-sm p-2 lg:text-lg w-1/4">Categoría</th>
                                <th class="text-sm p-2 lg:text-lg w-1/4">Pregunta</th>
                                <th class="text-sm p-2 lg:text-lg w-1/4">Respuesta</th>
                                <th class="text-sm p-2 lg:text-lg w-1/4">Total</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div id="table-body" class="w-full max-h-100 overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30">
                    <table class="table-fixed w-full">
                        <tbody class="">
                            <tr v-for="(item, i) in reportData" :key="i" class="text-white border-b border-neutral-400">
                                <td class="py-2 w-1/4">
                                    {{ item.category_name }}
                                </td>
                                <td class="py-2 w-1/4">
                                    {{ item.question_name }}
                                </td>
                                <td class="py-2 w-1/4">
                                    {{ item.answer_name }}
                                </td>
                                <td class="py-2 w-1/4 font-bold">
                                    {{ item.total }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <p v-else class="text-white/70 italic text-center py-4 bg-white/10 backdrop-blur-sm rounded-xl border border-blue-700/30">
                Cargando datos o sin resultados...
            </p>
        </div>
    </MainLayout>
</template>

<script setup>
import { getSurveys } from '@/composables/api/surveys';
import MainLayout from '@/layouts/main-layout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, onMounted, watch } from 'vue';

const page = usePage()

const selectedSurvey = ref(null)
const surveys = ref([])
const survey_selected = ref(null)

const reportData = ref([]);
onMounted(async () => {
    //if (!props.surveyId) return;

    try {
        // const { data } = await axios.get(`/api/results/report/${props.surveyId}`, {
        //     params: { category_id: props.categoryId }
        // });
        surveys.value = await allSurveys()
        
        // const { data } = await axios.get(`/api/result/report/${page.props.surveyId}`, {
        //     params: { category_id: page.props.categoryId }
        // });
        // reportData.value = data;
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
    console.log(survey_selected.value)
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