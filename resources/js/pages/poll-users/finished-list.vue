<template>
    <Head title="Encuestas Pendientes" />
    <MainLayout>
        <div class="fixed top-4 right-4 z-50 w-80">
            <NotificationBox
                :message="notification.message"
                :is-error="notification.isError"
            />
        </div>

        <div class="text-center">
            <h2 class="mt-8 text-3xl font-bold text-slate-900">
                Encuestas Pendientes
            </h2>
        </div>

        <div
            class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"
        >
            <table class="flex w-full border-collapse flex-col text-left text-slate-800">
                <thead>
                    <tr
                        class="flex border-b border-slate-200 bg-slate-50 text-xs tracking-wider text-slate-500 uppercase font-semibold"
                    >
                        <th class="w-1/3 p-4">Fecha</th>
                        <th class="w-1/2 p-4">Encuesta</th>
                        <th class="w-1/4 p-4 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody
                    class="custom-scrollbar block max-h-150 divide-y divide-slate-100 overflow-y-scroll"
                >
                    <tr
                        v-for="(survey, index) in displayedSurveys"
                        :key="index"
                        class="flex text-slate-800 transition-colors hover:bg-slate-50"
                    >
                        <td class="w-1/3 p-4 text-xs text-slate-600 md:text-sm">
                            {{ new Date(survey.created_at).toLocaleString() }}
                        </td>
                        <td class="w-1/2 p-4 text-xs font-medium text-slate-900 md:text-sm">
                            {{ survey?.survey?.name }}
                        </td>
                        <td
                            class="flex w-1/4 items-center justify-center p-4 text-center"
                        >
                            <Icon
                                v-if="survey.status === 'PENDIENTE'"
                                icon="mdi:clock-outline"
                                class="text-xl text-yellow-500"
                            />
                            <Icon
                                v-else-if="survey.status === 'FALLIDO'"
                                icon="mdi:close-circle-outline"
                                class="text-xl text-red-500"
                            />
                            <Icon
                                v-else-if="survey.status === 'GUARDADA'"
                                icon="mdi:check-circle-outline"
                                class="text-xl text-green-500"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="mx-auto mt-6 flex w-full gap-2 md:w-fit">
            <div class="w-fit">
                <button
                    @click="saveManyResults()"
                    class="green-button-app flex cursor-pointer items-center justify-center"
                    :disabled="pendingSurveys.length == 0 || isProcessing"
                >
                    <span
                        v-if="isProcessing"
                        class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                    ></span>
                    {{ isProcessing ? 'Procesando...' : 'Guardar Todas' }}
                </button>
            </div>
            <div class="w-fit">
                <button
                    @click="clearSavedSurveys()"
                    class="primary-button-app cursor-pointer"
                >
                    Limpiar Guardadas
                </button>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { Icon } from '@iconify/vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';
import NotificationBox from '@/components/notification-box.vue';
import { useBatchProcessor } from '@/composables/useBatchProcessor';
import MainLayout from '@/layouts/main-layout.vue';
const pendingSurveys = ref([]);
// Se guardan en orden de llegada (el procesamiento por lotes depende de ese
// orden); solo la vista se invierte para mostrar la más reciente primero.
const displayedSurveys = computed(() => [...pendingSurveys.value].reverse());
const { isProcessing, processBatch } = useBatchProcessor();
const notification = ref({ message: '', isError: false });

onMounted(() => {
    const data = localStorage.getItem('allSurveysPending');

    if (data) {
        pendingSurveys.value = JSON.parse(data);
    }
});

const showNotification = (msg, isError = false) => {
    notification.value = { message: msg, isError };
    setTimeout(() => {
        notification.value.message = '';
    }, 2500);
};

const saveManyResults = async () => {
    const pendingIndices = pendingSurveys.value
        .map((s, i) => (s.status !== 'GUARDADA' ? i : -1))
        .filter((i) => i !== -1);

    const surveysToProcess = pendingSurveys.value.filter(
        (s) => s.status !== 'GUARDADA',
    );
    const allData = surveysToProcess.map((s) => s.data);

    try {
        const report = await processBatch('/api/result/batch', {
            results: allData,
        });

        pendingIndices.forEach((originalIndex, reportIndex) => {
            pendingSurveys.value[originalIndex].status =
                report[reportIndex] === 'GUARDADA' ? 'GUARDADA' : 'FALLIDO';
        });

        localStorage.setItem(
            'allSurveysPending',
            JSON.stringify(pendingSurveys.value),
        );
        showNotification('Procesamiento finalizado');
    } catch (error) {
        console.error('Error en la petición batch:', error);
        showNotification('Error al procesar las encuestas', true);
    }
};

const clearSavedSurveys = () => {
    pendingSurveys.value = pendingSurveys.value.filter(
        (s) => s.status !== 'GUARDADA',
    );
    localStorage.setItem(
        'allSurveysPending',
        JSON.stringify(pendingSurveys.value),
    );
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
