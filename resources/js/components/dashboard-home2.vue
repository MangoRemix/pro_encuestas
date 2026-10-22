<template>
    <div
        id="cards"
        class="mt-3 mb-6 flex flex-wrap items-center justify-between gap-2 md:px-6 2xl:justify-start"
    >
        <DashboardCard
            class="w-full sm:w-42 md:w-1/4 lg:w-66"
            title="Encuestados"
            :value="summary.respondents"
        />
        <DashboardCard
            class="w-full sm:w-42 md:w-1/4 lg:w-66"
            title="Encuestadores"
            :value="summary.pollsters"
        />
        <DashboardCard
            class="w-full sm:w-42 md:w-1/4 lg:w-66"
            title="Respuestas"
            :value="summary.results"
        />
    </div>

    <div class="flex flex-wrap gap-6 md:px-6">
        <div
            class="min-w-[320px] flex-1 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"
        >
            <div
                class="flex items-center justify-between border-b border-slate-200 p-4"
            >
                <h2 class="text-lg font-bold text-slate-900">
                    Encuestas recientes
                </h2>
                <Link
                    href="/surveys"
                    class="text-sm font-medium text-blue-600 hover:text-blue-700"
                    >Ver todas</Link
                >
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-slate-800">
                    <thead
                        class="border-b border-slate-200 bg-slate-50 text-xs tracking-wider text-slate-500 uppercase"
                    >
                        <tr>
                            <th class="p-4 font-semibold">Nombre</th>
                            <th class="p-4 font-semibold text-nowrap">Creada</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="survey in recentSurveys"
                            :key="survey.id"
                            class="transition-colors hover:bg-slate-50"
                        >
                            <td class="p-4 text-xs font-medium text-slate-900 md:text-[13px]">
                                {{ survey.name }}
                            </td>
                            <td class="p-4 text-xs text-slate-500 md:text-[13px]">
                                {{ formatedDate(survey.created_at) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div
            class="min-w-fit flex-1 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm lg:max-w-105"
        >
            <div
                class="flex items-center justify-between border-b border-slate-200 p-4"
            >
                <h2 class="text-lg font-bold text-slate-900">
                    Top 5 Encuestadores
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-slate-800">
                    <thead
                        class="border-b border-slate-200 bg-slate-50 text-xs tracking-wider text-slate-500 uppercase"
                    >
                        <tr>
                            <th class="p-4 font-semibold">Encuestador</th>
                            <th class="p-4 text-center font-semibold">
                                Encuestas realizadas
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="pollster in topPollsters"
                            :key="pollster.pollster_id"
                            class="transition-colors hover:bg-slate-50"
                        >
                            <td class="p-4 text-xs font-medium text-slate-900 md:text-[13px]">
                                {{ pollster.pollster_name }}
                            </td>
                            <td class="p-4 text-center text-xs font-semibold text-slate-700 md:text-[13px]">
                                {{ pollster.total_surveys_conducted }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref } from 'vue';
import DashboardCard from '@/components/DashboardCard.vue';
import { formatedDate } from '@/composables/shared';
import { apiHost } from '@/store/store';

const recentSurveys = ref([]);
const topPollsters = ref([]);
const summary = ref({ respondents: 0, pollsters: 0, results: 0 });

onMounted(async () => {
    try {
        const [surveysRes, pollstersRes, summaryRes] = await Promise.all([
            axios.get(`${apiHost}survey/show-recent`),
            axios
                .get(`${apiHost}result/reports/top-pollsters`)
                .catch(() => ({ data: [] })),
            axios.get(`${apiHost}dashboard/summary`),
        ]);

        recentSurveys.value = surveysRes.data.length > 0 ? surveysRes.data : [];

        topPollsters.value =
            pollstersRes.data.length > 0 ? pollstersRes.data : [];

        summary.value = summaryRes.data;
    } catch (error) {
        console.error('Error al cargar datos del dashboard:', error);
    }
});
</script>
<style scoped></style>
