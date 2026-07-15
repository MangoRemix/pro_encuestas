<template>
    <Head title="Encuestas Pendientes" />
    <MainLayout>
        <div class="text-center">
            <h2 class="text-3xl text-white font-bold mt-8">Encuestas Pendientes</h2>
        </div>

        <div class="bg-white/30 backdrop-blur-md shadow-lg rounded-xl p-6 border border-blue-700/50 w-full h-135 mt-6">
            <div id="table-header" class="h-10 w-full mb-3">
                <table class="table-fixed w-full text-left">
                    <thead>
                        <tr class="border-b border-white/30 text-white text-lg">
                            <th class="p-2 w-1/4">Fecha</th>
                            <th class="p-2 w-1/4">Encuesta</th>
                            <th class="p-2 w-1/4">Cantidad Respuestas</th>
                            <th class="p-2 w-1/4">Estado</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div id="table-body" class="w-full max-h-100 overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30">
                <table class="table-fixed w-full">
                    <tbody>
                        <tr v-for="(survey, index) in pendingSurveys" :key="index" class="text-white border-b border-neutral-400">
                            <td class="py-3 px-2 w-1/4">{{ new Date(survey.created_at).toLocaleString() }}</td>
                            <td class="py-3 px-2 w-1/4">{{ survey?.survey?.name}}</td>
                            <td class="py-3 px-2 w-1/4">{{ survey.data.length }}</td>
                            <td class="py-3 px-2 w-1/4">
                                <span :class="`px-2 py-1 ${survey.status=='PENDIENTE'? 'bg-yellow-600':'bg-green-600'}  rounded text-xs`">{{ survey.status }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="w-1/5 mx-auto">
            <button @click="saveManyResults()" class="green-button-app cursor-pointer" :disabled="pendingSurveys.length==0">
                Guardar Todas
            </button>
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/layouts/main-layout.vue';
import axios from 'axios';

const pendingSurveys = ref([]);

onMounted(() => {
    const data = localStorage.getItem('allSurveysPending');
    if (data) {
        pendingSurveys.value = JSON.parse(data);
    }
});

const saveManyResults = async ()=>{
    try {
        for await (const pendingSurvey of pendingSurveys.value) {
            const {data,error,status} = await axios.post('/api/result/batch', { results: pendingSurvey.data });

            if(status == 201){
                pendingSurvey.status = 'GUARDADA'
            }


        }
        
    } catch (error) {
        console.error(error)
    }
    
}
</script>
