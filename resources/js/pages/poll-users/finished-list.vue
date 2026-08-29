<template>
    <Head title="Encuestas Pendientes" />
    <MainLayout>
        <div class="text-center">
            <h2 class="text-3xl text-white font-bold mt-8 underline">Encuestas Pendientes</h2>
        </div>

        <div class="mt-6 bg-gray-500/50 border border-slate-700 rounded-lg overflow-hidden">
            <table class="w-full text-left border-collapse flex flex-col">
                <thead>
                        <tr class="border-b border-slate-700 bg-slate-900/50 text-white text-xs uppercase tracking-wider flex">
                            <th class="p-4 w-1/3">Fecha</th>
                            <th class="p-4 w-1/2">Encuesta</th>
                            <th class="p-4 w-1/4 text-center">Estado</th>
                        </tr>
                    </thead>
                <tbody class="divide-y divide-slate-700/50 block max-h-150 overflow-y-scroll custom-scrollbar">
                        <tr v-for="(survey, index) in pendingSurveys" :key="index" class="text-slate-200 hover:bg-slate-600/30 transition-colors flex">
                            <td class="p-4 w-1/3 text-xs md:text-sm">{{ new Date(survey.created_at).toLocaleString() }}</td>
                            <td class="p-4 w-1/2 text-xs md:text-sm">{{ survey?.survey?.name }}</td>
                            <td class="p-4 w-1/4 text-center flex justify-center items-center">
                                    <Icon v-if="survey.status === 'PENDIENTE'" icon="mdi:clock-outline" class="text-yellow-500 text-xl" />
                                    <Icon v-else-if="survey.status === 'FALLIDO'" icon="mdi:close-circle-outline" class="text-red-500 text-xl" />
                                    <Icon v-else-if="survey.status === 'GUARDADA'" icon="mdi:check-circle-outline" class="text-green-500 text-xl" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <div class="w-full md:w-1/3 mx-auto flex gap-2 mt-6">
            <div class="w-1/2">
                <button
                    @click="saveManyResults()"
                    class="green-button-app cursor-pointer flex items-center justify-center"
                    :disabled="pendingSurveys.length == 0 || isProcessing"
                >
                    <span v-if="isProcessing" class="animate-spin mr-2 border-2 border-white border-t-transparent rounded-full w-4 h-4"></span>
                    {{ isProcessing ? 'Procesando...' : 'Guardar Todas' }}
                </button>
            </div>
            <div class="w-1/2">
                <button @click="clearSavedSurveys()" class="cursor-pointer primary-button-app">
                    Limpiar Guardadas
                </button>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
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

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(30, 41, 59, 0.5);
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #475569;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}
</style>

