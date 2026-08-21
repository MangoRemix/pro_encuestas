<template>
    <div class="p-6">
        <div v-if="categories && categories.length > 0"
          class="flex flex-wrap items-center justify-center gap-2"
          >           
          <div v-for="category in categories" :key="category.id" class="mb-10 w-full">
                <h3 class="text-center text-blue-400 text-lg font-semibold mb-2 underline-offset-4 underline">{{ category.name }}</h3>
                
                <div v-for="question in category.questions" :key="question.id" class="bg-neutral-800 p-6 rounded-xl border border-blue-700/30 mt-5 min-h-120 overflow-y-scroll">
                    
                  <BarChart
                    title-color="#ffffff"
                    legend-color="#ffffff"
                    x-scale-color="#ffffff"
                    y-scale-color="#ffffff"
                    :chart-data="getChartData(question)"
                    :chart-options="{
                        indexAxis: 'y',
                        maintainAspectRatio: false,
                        responsive: true,
                        scales: {
                            x: { ticks: { font: { size: 14, weight: 'bold' } } },
                            y: { ticks: { font: { size: 14, weight: 'bold' } } }
                        },
                        plugins: {
                            legend: {
                                labels: {
                                    font: {
                                        size: 14,
                                    }
                                }
                            }
                        },
                    }"
                  />
                </div>
            </div>
        </div>
        <p v-else class="text-white/70 italic text-center py-4 bg-white/10 backdrop-blur-sm rounded-xl border border-blue-700/30">
            Cargando datos o sin resultados...
        </p>
    </div>
</template>

<script setup>
import { watch } from 'vue';
import BarChart from '@/components/Charts/BarChart.vue';

const props = defineProps({
    categories: {
        type: Array,
        default: () => []
    }
});

watch(() => props.categories, (newVal) => {
    console.log("GraphicsCopy recibió categorías:", newVal);
}, { immediate: true });

const getChartData = (question) => {
    return {
        labels: question.answers.map(a => a.name?.toUpperCase()),
        datasets: [{
            label: question.name?.toUpperCase(),          
            data: question.answers.map(a => a.total_votes),
            backgroundColor: ['#3b82f6', '#3b15f6', '#3b8218', '#E582f6'],
            borderRadius: 4,
        }]
    };
};
</script>
<style scoped>
    
</style>