<template>
    <div class="lg:pl-12">
        <h1 class="text-3xl lg:text-4xl text-blue-100 font-extrabold">
            Dashboard
        </h1>
    </div>

    <div id="cards" class="flex items-center flex-wrap justify-around gap-4 mb-6 md:p-6">
        <DashboardCard title="Usuarios" value="1,234" />
        <DashboardCard title="Encuestas" value="48" />
        <DashboardCard title="Respuestas" value="12,890" />
    </div>
    
    <div class="flex flex-wrap gap-6 md:p-6">
        <div class="flex-1 min-w-[320px] bg-slate-800 border border-slate-700 rounded-lg overflow-hidden">
            <div class="p-4 border-b border-slate-700 flex justify-between items-center">
                <h2 class="text-lg font-bold text-white">Encuestas recientes</h2>
                <Link href="/surveys" class="text-sm text-blue-400 hover:text-blue-300">Ver todas</Link>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-900/50 text-white text-xs uppercase tracking-wider">
                        <tr>
                            <th class="p-4">Nombre</th>
                            <th class="p-4 text-nowrap">Fecha de inicio</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        <tr v-for="survey in recentSurveys" :key="survey.id" class="text-slate-200 hover:bg-slate-600/30 transition-colors">
                            <td class="p-4 font-medium text-xs md:text-[13px]">{{ survey.name }}</td>
                            <td class="p-4 text-xs md:text-[13px]">{{ formatedDate(survey.init_date) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex-1 min-w-fit lg:max-w-105 bg-slate-800 border border-slate-700 rounded-lg overflow-hidden">
            <div class="p-4 border-b border-slate-700 flex justify-between items-center">
                <h2 class="text-lg font-bold text-white">Top 5 Encuestadores</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-900/50 text-white text-xs uppercase tracking-wider">
                        <tr>
                            <th class="p-4">Encuestador</th>
                            <th class="p-4 text-center">Encuestas realizadas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        <tr v-for="pollster in topPollsters" :key="pollster.pollster_id" class="text-slate-200 hover:bg-slate-600/30 transition-colors">
                            <td class="p-4 font-medium text-xs md:text-[13px]">{{ pollster.pollster_name }}</td>
                            <td class="p-4 text-xs md:text-[13px] text-center">{{ pollster.total_surveys_conducted }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { apiHost } from '@/store/store';
import { formatedDate } from '@/composables/shared';
import DashboardCard from '@/components/DashboardCard.vue';

const recentSurveys = ref([]);
const topPollsters = ref([]);

onMounted(async () => {
    try {
        const [surveysRes, pollstersRes] = await Promise.all([
            axios.get(`${apiHost}survey/show-recent`),
            axios.get(`${apiHost}result/reports/top-pollsters`)
        ]);
        recentSurveys.value = surveysRes.data;
        topPollsters.value = pollstersRes.data;
    } catch (error) {
        console.error("Error al cargar datos del dashboard:", error);
    }
});
</script>
<style scoped>
    
</style>