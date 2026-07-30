<script setup>
import { computed } from 'vue'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
} from 'chart.js'
import { Bar } from 'vue-chartjs'

// Registrar componentes de Chart.js
ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

const props = defineProps({
  chartData: {
    type: Object,
    required: true
  },
  chartOptions: {
    type: Object,
    default: () => ({
      responsive: true,
      maintainAspectRatio: false
    })
  }
})

// Unimos las opciones por defecto con las que envíe el padre
const mergedOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: true, // Mantiene la escala
  ...props.chartOptions,
  plugins: {
    legend: {
      position: 'top',
    },
    ...props.chartOptions.plugins
  }
}))
</script>

<template>
  
    <Bar :data="chartData" :options="mergedOptions" />
  
</template>
