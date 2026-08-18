<template>
    <div class="p-6">
        
        <div v-if="Object.keys(groupedReportData).length > 0" 
          class="flex flex-wrap items-center justify-center gap-2"
          >           
          <div v-for="(questions, categoryName) in groupedReportData" :key="categoryName" class="mb-10 w-140">
                <h3 class="text-center text-blue-400 text-lg font-semibold mb-2 underline-offset-4 underline">{{ categoryName }}</h3>
                
                <div v-for="(answers, questionName) in questions" :key="questionName" class="bg-neutral-800 p-6 rounded-xl border border-blue-700/30 mt-5 h-120 overflow-x-scroll">
                    
                    
                  <BarChart title-color="#ffffff" legend-color="#ffffff" x-scale-color="#ffffff" y-scale-color="#ffffff" :chart-data="chartData(answers, questionName)" />
                    
                    
                </div>
            </div>
        </div>
        <p v-else class="text-white/70 italic text-center py-4 bg-white/10 backdrop-blur-sm rounded-xl border border-blue-700/30">
            Cargando datos o sin resultados...
        </p>
        
        
    </div>
</template>
<script setup>
import { computed } from 'vue';
import BarChart from '@/components/Charts/BarChart.vue';

const props = defineProps(['reportData']);

// Función para agrupar por una clave específica
const groupBy = (array, keys) => {
  return array.reduce((result, currentValue) => {
    // Si 'keys' es un string, lo convertimos a array para soportar ambos casos
    const keyArray = Array.isArray(keys) ? keys : [keys];
    
    let currentLevel = result;
    
    keyArray.forEach((key, index) => {
      const groupKey = currentValue[key];
      const isLast = index === keyArray.length - 1;

      if (!currentLevel[groupKey]) {
        currentLevel[groupKey] = isLast ? [] : {};
      }

      if (isLast) {
        currentLevel[groupKey].push(currentValue);
      } else {
        currentLevel = currentLevel[groupKey];
      }
    });

    return result;
  }, {});
};

// Propiedad computada para los datos agrupados por 'category_name'
const groupedReportData = computed(() => {
  if (Array.isArray(props.reportData)) {
    return groupBy(props.reportData, ['category_name','question_name']);
  }
  return {}; 
});

// Transformar datos agrupados para el gráfico
const chartData = (item, index) => {
    return {
        labels: item.map(i => i.answer_name),
        datasets: [{
            label: index,
            data: item.map(i => i.total),
            backgroundColor: ['#3b82f6','#3b15f6','#3b8218', '#E582f6'],
            borderRadius: 4
        }]
    };
};
</script>
<style scoped>
    
</style>