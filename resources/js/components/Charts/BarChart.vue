<script setup>
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    LineElement,
    PointElement,
    ArcElement,
    CategoryScale,
    LinearScale,
} from 'chart.js';
import { computed } from 'vue';
import { Bar, Pie, Line } from 'vue-chartjs';

// Registrar componentes de Chart.js
ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    LineElement,
    PointElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
);

const props = defineProps({
    // 'bar' | 'pie' | 'line' — el mismo componente sirve para los 3 tipos
    // de gráfica para que quien lo use no tenga que decidir entre 3
    // componentes distintos, solo cambiar este prop.
    chartType: {
        type: String,
        default: 'bar',
    },
    chartData: {
        type: Object,
        required: true,
    },
    chartOptions: {
        type: Object,
        default: () => ({
            responsive: true,
            maintainAspectRatio: false,
        }),
    },
    titleColor: {
        type: String,
        default: '#666',
    },
    legendColor: {
        type: String,
        default: '#666',
    },
    xScaleColor: {
        type: String,
        default: '#666',
    },
    yScaleColor: {
        type: String,
        default: '#666',
    },
});

const chartComponent = computed(
    () => ({ bar: Bar, pie: Pie, line: Line })[props.chartType] || Bar,
);

/**
 * Todas las gráficas muestran el % del total, pero el tooltip también
 * necesita la cantidad cruda (cuántos encuestados representa ese %) para
 * que se entienda la magnitud real detrás del porcentaje. Cada chart data
 * le agrega un array paralelo `rawCounts` al dataset con esa cantidad.
 */
const defaultTooltipLabel = (context) => {
    const raw = context.dataset.rawCounts?.[context.dataIndex];
    const value =
        typeof context.parsed === 'object'
            ? (context.parsed.y ?? context.parsed.x ?? context.parsed.r)
            : context.parsed;
    const pct = typeof value === 'number' ? value.toFixed(2) : value;
    const label = context.dataset.label ? `${context.dataset.label}: ` : '';

    return raw !== undefined ? `${label}${pct}% (${raw})` : `${label}${pct}%`;
};

// Unimos las opciones por defecto con las que envíe el padre
const mergedOptions = computed(() => {
    const options = {
        responsive: true,
        maintainAspectRatio: false, // Force false globally to allow flexible containers
        ...props.chartOptions,
        animation: {
            duration: 750,
            easing: 'easeInOutQuart',
        },
        resizeDelay: 100, // Debounce resize for smoother transitions
        plugins: {
            ...props.chartOptions?.plugins,
            legend: {
                position: 'top',
                ...props.chartOptions?.plugins?.legend,
                labels: {
                    color: props.legendColor,
                    ...props.chartOptions?.plugins?.legend?.labels,
                },
            },
            title: {
                display: !!props.chartOptions?.plugins?.title?.text,
                color: props.titleColor,
                ...props.chartOptions?.plugins?.title,
            },
            tooltip: {
                ...props.chartOptions?.plugins?.tooltip,
                callbacks: {
                    label: defaultTooltipLabel,
                    ...props.chartOptions?.plugins?.tooltip?.callbacks,
                },
            },
        },
    };

    // La torta no usa ejes cartesianos — pasarle "scales" no rompe nada,
    // pero no tiene sentido y por eso se omite.
    if (props.chartType !== 'pie') {
        options.scales = {
            ...props.chartOptions?.scales,
            x: {
                ...props.chartOptions?.scales?.x,
                ticks: {
                    color: props.xScaleColor,
                    ...props.chartOptions?.scales?.x?.ticks,
                },
                grid: {
                    color: props.xScaleColor,
                    ...props.chartOptions?.scales?.x?.grid,
                },
            },
            y: {
                ...props.chartOptions?.scales?.y,
                ticks: {
                    color: props.yScaleColor,
                    ...props.chartOptions?.scales?.y?.ticks,
                },
                grid: {
                    color: props.yScaleColor,
                    ...props.chartOptions?.scales?.y?.grid,
                },
            },
        };
    }

    return options;
});
</script>

<template>
    <component
        :is="chartComponent"
        :data="chartData"
        :options="mergedOptions"
    />
</template>
