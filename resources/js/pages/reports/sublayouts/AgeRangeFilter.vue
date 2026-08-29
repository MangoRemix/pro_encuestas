<script setup>
import { ref, computed } from 'vue';
import { Bar } from 'vue-chartjs';
import { 
    Chart as ChartJS, 
    Title, 
    Tooltip, 
    Legend, 
    BarElement, 
    CategoryScale, 
    LinearScale 
} from 'chart.js';
import axios from 'axios';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps({
    surveyId: { type: [Number, String], required: true },
    totalRespondent: { type: Number, required: true }
});

console.log(props)

const min = ref('');
const max = ref('');
const count = ref(0);
const loading = ref(false);

const fetchData = async () => {
    loading.value = true;
    try {
        const minVal = min.value === '' ? '*' : min.value;
        const maxVal = max.value === '' ? '*' : max.value;
        
        const response = await axios.get(`/api/result/age-range/${props.surveyId}`, {
            params: { min: minVal, max: maxVal }
        });
        console.log(response.data)
        count.value = response.data.count || 0;
        console.log("count",count.value)
    } catch (error) {
        console.error("Error fetching data:", error);
    } finally {
        loading.value = false;
    }
};

const chartData = computed(() => ({
    labels: ['Participación por Rango de Edad'],
    datasets: [{
        label: 'Porcentaje de encuestados',
        backgroundColor: '#6366f1',
        data: [props.totalRespondent > 0 ? ((count.value / props.totalRespondent) * 100).toFixed(2) : 0]
    }]
}));

const chartOptions = {
    responsive: true,
    scales: {
        y: { beginAtZero: true, max: 100 }
    }
};
</script>

<template>
    <div class="p-6 bg-white rounded-xl shadow-sm border border-slate-200">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Filtro por Rango de Edad</h3>
        
        <div class="flex flex-wrap gap-x-3 mb-6">
            <input v-model="min" type="number" placeholder="Edad Min" class="border border-slate-300 p-2 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none w-28" />
            <input v-model="max" type="number" placeholder="Edad Max" class="border border-slate-300 p-2 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none w-28" />
            <button 
                @click="fetchData" 
                :disabled="loading"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition-colors"
            >
                {{ loading ? 'Cargando...' : 'Filtrar' }}
            </button>
        </div>

        <div v-if="count >= 0" class="h-64">
            <Bar :data="chartData" :options="chartOptions" />
            <p class="mt-4 text-center text-slate-600 font-medium">
                Total en rango: <span class="text-indigo-600">{{ count }}</span> 
                ({{ props.totalRespondent > 0 ? ((count / props.totalRespondent) * 100).toFixed(1) : 0 }}% del total)
            </p>
        </div>
    </div>
</template>
