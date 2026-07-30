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
  },
  titleColor: {
    type: String,
    default: '#666'
  },
  legendColor: {
    type: String,
    default: '#666'
  },
  xScaleColor: {
    type: String,
    default: '#666'
  },
  yScaleColor: {
    type: String,
    default: '#666'
  }
})

// Unimos las opciones por defecto con las que envíe el padre
const mergedOptions = computed(() => {
  const options = {
    responsive: true,
    maintainAspectRatio: true,
    ...props.chartOptions,
    plugins: {
      ...props.chartOptions?.plugins,
      legend: {
        position: 'top',
        ...props.chartOptions?.plugins?.legend,
        labels: {
          color: props.legendColor,
          ...props.chartOptions?.plugins?.legend?.labels
        }
      },
      title: {
        display: !!props.chartOptions?.plugins?.title?.text,
        color: props.titleColor,
        ...props.chartOptions?.plugins?.title
      }
    },
    scales: {
      ...props.chartOptions?.scales,
      x: {
        ...props.chartOptions?.scales?.x,
        ticks: {
          color: props.xScaleColor,
          ...props.chartOptions?.scales?.x?.ticks
        },
        grid: {
          color: props.xScaleColor,
          ...props.chartOptions?.scales?.x?.grid
        }
      },
      y: {
        ...props.chartOptions?.scales?.y,
        ticks: {
          color: props.yScaleColor,
          ...props.chartOptions?.scales?.y?.ticks
        },
        grid: {
          color: props.yScaleColor,
          ...props.chartOptions?.scales?.y?.grid
        }
      }
    }
  }
  return options
})
</script>

<template>
  
    <Bar :data="chartData" :options="mergedOptions" />
  
</template>
