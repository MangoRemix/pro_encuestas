<template>
    <div class="bg-neutral-800 p-6 rounded-xl border border-blue-700/30 mt-5 max-h-120">
        <h3 class="text-center text-blue-400 text-lg font-semibold mb-4">Encuestados por Sexo</h3>
        <BarChart
            v-if="chartData"
            title-color="#ffffff"
            legend-color="#ffffff"
            x-scale-color="#ffffff"
            y-scale-color="#ffffff"
            :chart-data="chartData"
            :chart-options="{
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }"
        />
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import BarChart from '@/components/Charts/BarChart.vue';
import { getRespondentCountBySex } from '@/composables/api/reports';

const props = defineProps({
    surveyId: Number,
    totalRespondent: Number
});
const chartData = ref(null);

const loadData = async () => {
    if (!props.surveyId || !props.totalRespondent) return;
    const { data } = await getRespondentCountBySex(props.surveyId);
    if (data) {
        chartData.value = {
            labels: data.map(item => item.sex_id == 1 ? 'Masculino' : (item.sex_id == 2 ? 'Femenino' : 'Otro')),
            datasets: [{
                label: '% del Total',
                data: data.map(item => ((item.total_respondents / props.totalRespondent) * 100).toFixed(2)),
                backgroundColor: ['#3b82f6', '#ec4899', '#8b5cf6'],
                borderRadius: 4,
            }]
        };
    }
};

watch(() => [props.surveyId, props.totalRespondent], loadData, { immediate: true });
</script>

