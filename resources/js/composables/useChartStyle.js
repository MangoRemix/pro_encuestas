/**
 * Traduce el selector global "Estilo de gráfica" (uno solo para toda la
 * página de Reportes) al chartType + indexAxis que le corresponde a
 * BarChart.vue. 'default' deja cada gráfica con su apariencia original
 * (la que tenía antes de que existiera este selector) — solo cambia algo
 * cuando el usuario elige explícitamente torta/líneas/barras.
 */
export function resolveChartStyle(
    chartStyle,
    defaultType = 'bar',
    defaultIndexAxis = 'x',
) {
    switch (chartStyle) {
        case 'bar-h':
            return { chartType: 'bar', indexAxis: 'y' };
        case 'bar-v':
            return { chartType: 'bar', indexAxis: 'x' };
        case 'pie':
            return { chartType: 'pie', indexAxis: undefined };
        case 'line':
            return { chartType: 'line', indexAxis: undefined };
        default:
            return { chartType: defaultType, indexAxis: defaultIndexAxis };
    }
}

export const CHART_STYLE_OPTIONS = [
    { value: 'default', label: 'Estilo original' },
    { value: 'bar-h', label: 'Barras horizontales' },
    { value: 'bar-v', label: 'Barras verticales' },
    { value: 'pie', label: 'Torta' },
    { value: 'line', label: 'Líneas' },
];
