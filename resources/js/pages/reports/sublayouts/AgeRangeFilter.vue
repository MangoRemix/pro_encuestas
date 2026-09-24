<script setup>
import axios from 'axios';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
} from 'chart.js';
import { ref, computed } from 'vue';
import { Bar } from 'vue-chartjs';

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
);

const props = defineProps({
    surveyId: { type: [Number, String], required: true },
    totalRespondent: { type: Number, required: true },
    activityId: { type: [Number, String], default: '' },
    parishId: { type: [Number, String], default: '' },
    pollsterId: { type: [Number, String], default: '' },
    dateFrom: { type: String, default: '' },
    dateTo: { type: String, default: '' },
});

const min = ref('');
const max = ref('');
const count = ref(0);
const loading = ref(false);

const fetchData = async () => {
    loading.value = true;

    try {
        const minVal = min.value === '' ? '*' : min.value;
        const maxVal = max.value === '' ? '*' : max.value;

        const response = await axios.get(
            `/api/result/age-range/${props.surveyId}`,
            {
                params: {
                    min: minVal,
                    max: maxVal,
                    activity_id: props.activityId || undefined,
                    parish_id: props.parishId || undefined,
                    pollster_id: props.pollsterId || undefined,
                    from: props.dateFrom || undefined,
                    to: props.dateTo || undefined,
                },
            },
        );
        count.value = response.data.count || 0;
    } catch (error) {
        console.error('Error fetching data:', error);
    } finally {
        loading.value = false;
    }
};

const chartData = computed(() => ({
    labels: ['Participación por Rango de Edad'],
    datasets: [
        {
            label: 'Porcentaje de encuestados',
            backgroundColor: '#6366f1',
            data: [
                props.totalRespondent > 0
                    ? ((count.value / props.totalRespondent) * 100).toFixed(2)
                    : 0,
            ],
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    animation: {
        duration: 750,
        easing: 'easeInOutQuart',
    },
    resizeDelay: 100,
    scales: {
        y: { beginAtZero: true, max: 100 },
    },
};
</script>

<template>
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-lg font-semibold text-slate-800">
            Filtro por Rango de Edad
        </h3>

        <div class="mb-6 flex flex-wrap gap-x-3">
            <input
                v-model="min"
                type="number"
                placeholder="Edad Min"
                class="w-28 rounded-lg border border-slate-300 p-2 outline-none focus:ring-2 focus:ring-indigo-500"
            />
            <input
                v-model="max"
                type="number"
                placeholder="Edad Max"
                class="w-28 rounded-lg border border-slate-300 p-2 outline-none focus:ring-2 focus:ring-indigo-500"
            />
            <button
                @click="fetchData"
                :disabled="loading"
                class="rounded-lg bg-indigo-600 px-6 py-2 text-white transition-colors hover:bg-indigo-700"
            >
                {{ loading ? 'Cargando...' : 'Filtrar' }}
            </button>
        </div>

        <div v-if="count >= 0" class="h-64">
            <Bar :data="chartData" :options="chartOptions" />
            <p class="mt-4 text-center font-medium text-slate-600">
                Total en rango:
                <span class="text-indigo-600">{{ count }}</span> ({{
                    props.totalRespondent > 0
                        ? ((count / props.totalRespondent) * 100).toFixed(1)
                        : 0
                }}% del total)
            </p>
        </div>
    </div>
</template>
