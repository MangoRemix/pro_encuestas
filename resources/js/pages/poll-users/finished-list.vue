<template>
    <Head title="Encuestas Pendientes" />
    <MainLayout>
        <div class="text-center">
            <h2 class="text-3xl text-white font-bold mt-8 underline">Encuestas Pendientes</h2>
        </div>

        <div class="bg-white/30 backdrop-blur-md shadow-lg rounded-xl p-6 border border-blue-700/50 w-full h-135 mt-6">
            <div id="table-header" class="h-10 w-full mb-3">
                <table class="table-fixed w-full text-left">
                    <thead>
                        <tr class="border-b border-white/30 text-white text-lg">
                            <th class="p-2 w-1/3">Fecha</th>
                            <th class="p-2 w-1/3">Encuesta</th>
                            <!-- <th class="p-2 w-1/4">Cantidad Respuestas</th> -->
                            <th class="p-2 w-1/3 text-center">Estado</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div id="table-body" class="w-full max-h-100 overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30">
                <table class="table-fixed w-full">
                    <tbody>
                        <tr v-for="(survey, index) in pendingSurveys" :key="index" class="text-white border-b border-neutral-400">
                            <td class="py-3 px-2 w-1/3">{{ new Date(survey.created_at).toLocaleString() }}</td>
                            <td class="py-3 px-2 w-1/3">{{ survey?.survey?.name}}</td>
                            <!-- <td class="py-3 px-2 w-1/4">{{ survey.data.length }}</td> -->
                            <td class="py-3 px-2 w-1/3 text-center">
                                <span :class="`px-2 py-1 ${survey.status=='PENDIENTE'? 'bg-yellow-600':survey.status=='FALLIDO'?'bg-red-600':'bg-green-600'}  rounded text-xs`">{{ survey.status }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="w-full md:w-1/3 mx-auto flex gap-2">
            <button
                @click="saveManyResults()"
                class="green-button-app cursor-pointer flex items-center justify-center"
                :disabled="pendingSurveys.length == 0 || isProcessing"
            >
                <span v-if="isProcessing" class="animate-spin mr-2 border-2 border-white border-t-transparent rounded-full w-4 h-4"></span>
                {{ isProcessing ? 'Procesando...' : 'Guardar Todas' }}
            </button>
            <button @click="clearSavedSurveys()" class="cursor-pointer primary-button-app">
                Limpiar Guardadas
            </button>
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/layouts/main-layout.vue';
import { useBatchProcessor } from '@/composables/useBatchProcessor';
const pendingSurveys = ref([]);
const { isProcessing, processBatch } = useBatchProcessor();

onMounted(() => {
    const data = localStorage.getItem('allSurveysPending');
    if (data) {
        pendingSurveys.value = JSON.parse(data);
    }
});

const saveManyResults = async () => {
    const pendingIndices = pendingSurveys.value
        .map((s, i) => s.status !== 'GUARDADA' ? i : -1)
        .filter(i => i !== -1);

    const surveysToProcess = pendingSurveys.value.filter(s => s.status !== 'GUARDADA');
    const allData = surveysToProcess.map(s => s.data);

    try {
        const report = await processBatch('/api/result/batch', { results: allData });
        
        pendingIndices.forEach((originalIndex, reportIndex) => {
            pendingSurveys.value[originalIndex].status = report[reportIndex] === 'GUARDADA' ? 'GUARDADA' : 'FALLIDO';
        });
        
        localStorage.setItem('allSurveysPending', JSON.stringify(pendingSurveys.value));
        alert("Procesamiento finalizado");
    } catch (error) {
        console.error("Error en la petición batch:", error);
    }
}

const clearSavedSurveys = () => {
    pendingSurveys.value = pendingSurveys.value.filter(s => s.status !== 'GUARDADA');
    localStorage.setItem('allSurveysPending', JSON.stringify(pendingSurveys.value));
}
</script>

