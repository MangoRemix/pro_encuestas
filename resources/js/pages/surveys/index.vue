<template>
    <Head title="Encuestas: detalles" />
    <MainLayout>
        <div class=" text-center">
            <h2 class="text-3xl text-white font-bold mt-8 underline">Encuestas</h2>
        </div>
        <div class="w-full flex flex-col-reverse md:flex-row justify-between items-center mb-6 gap-4">
            <div class="w-full md:w-80">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar encuesta..."
                    class="w-full px-4 py-2 rounded bg-slate-900 border border-slate-700 text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 transition-all"
                />
            </div>
            <div class="flex w-full md:w-auto gap-2 ">
                <div class="w-40">
                    <button
                    @click="importSurvey"
                    :disabled="isProcessing"
                    class="yellow-button-app flex items-center gap-x-2 justify-center cursor-pointer"
                >
                    <span v-if="isProcessing" class="animate-spin border-2 border-white border-t-transparent rounded-full w-4 h-4"></span>
                    <Icon v-else class="text-2xl" icon="ic:outline-file-upload" />
                    {{ isProcessing ? 'Importando...' : 'Importar' }}
                </button>
                </div>
                <!-- <button
                    @click="importSurvey"
                    :disabled="isProcessing"
                    class="flex-1 md:flex-none flex justify-center items-center rounded px-4 py-2 text-white bg-green-600 hover:bg-green-500 font-medium transition-colors disabled:opacity-50"
                >
                    <span v-if="isProcessing" class="animate-spin mr-2 border-2 border-white border-t-transparent rounded-full w-4 h-4"></span>
                    <Icon v-else class="text-xl mr-2" icon="ic:outline-file-upload" />
                    {{ isProcessing ? 'Importando...' : 'Importar' }}
                </button> -->
                <div class="w-50 flex items-center" >
                    <Link href="/surveys/create-survey/step-1" class="green-button-app flex items-center gap-x-2 justify-center">  
                        <Icon class="text-2xl " icon="ic:outline-plus" /> Crear manual 
                    </Link>
                    <!-- <button class="green-button-app flex items-center justify-center cursor-pointer gap-x-2"
                    @click="idSurveyToEdit=0; isModalOpen=true;"> 
                        <Icon class="text-2xl " icon="ic:outline-plus" />
                        Crear manual
                    </button> -->
                    
                </div>
                <!-- <button
                    @click="idSurveyToEdit=0; isModalOpen=true;"
                    class="flex-1 md:flex-none flex justify-center items-center rounded px-4 py-2 text-white bg-yellow-600 hover:bg-yellow-500 font-medium transition-colors"
                >
                    <Icon class="text-xl mr-2" icon="ic:outline-plus" />
                    Crear manual
                </button> -->
            </div>
        </div>

        <!-- Vista Móvil: Tarjetas -->
        <div class="md:hidden flex flex-wrap gap-2 mb-4">
            <button
                v-for="field in sortableFields"
                :key="field.key"
                @click="toggleSort(field.key)"
                class="px-3 py-1 rounded-full text-xs font-medium border transition-colors"
                :class="sortField === field.key
                    ? 'bg-blue-600 border-blue-500 text-white'
                    : 'bg-slate-800 border-slate-700 text-slate-400'"
            >
                {{ field.label }}
                <span v-if="sortField === field.key">
                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                </span>
            </button>
        </div>
        <div class="md:hidden space-y-4">
            <div v-for="survey in filteredSurveys" :key="survey.id" class="bg-slate-800 p-4 rounded-lg border border-slate-700 shadow-sm">
                <div class="flex justify-between items-start mb-3">
                    <h3 class="font-bold text-white text-lg">{{ survey.name }}</h3>
                    <span class="text-xs font-semibold bg-slate-700 text-slate-300 px-2 py-1 rounded-full text-nowrap">
                        {{ survey.results_count }} respuestas
                    </span>
                </div>
                <div class="text-sm text-slate-400 space-y-1 mb-4">
                    <p>Inicio: {{ formatedDate(survey.init_date) }}</p>
                    <p>Fin: {{ formatedDate(survey.finish_date) }}</p>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-700">
                    <Link :href="`/surveys/details/${survey.id}`" class="p-3 text-blue-400 hover:bg-slate-700 rounded-lg">
                        <Icon class="text-2xl" icon="ic:baseline-remove-red-eye"/>
                    </Link>
                    <button @click="idSurveyToEdit=survey.id; isModalOpen=true;" class="p-3 text-yellow-500 hover:bg-slate-700 rounded-lg">
                        <Icon class="text-2xl" icon="ic:baseline-edit"/>
                    </button>
                    <button class="p-3 text-red-500 hover:bg-slate-700 rounded-lg">
                        <Icon class="text-2xl" icon="ic:baseline-restore-from-trash"/>
                    </button>
                </div>
            </div>
        </div>

        <!-- Vista Escritorio: Tabla -->
        <div class="hidden md:block bg-gray-500/50 border border-slate-700 rounded-lg overflow-hidden">
            <div class="max-h-150 overflow-y-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-slate-900 z-10">
                        <tr class="border-b border-slate-700 text-white text-xs uppercase tracking-wider">
                            <th class="p-4 cursor-pointer select-none" @click="toggleSort('name')">
                                <div class="flex items-center gap-2">
                                    Nombre
                                    <SortIcon field="name" :current-field="sortField" :direction="sortDirection" />
                                </div>
                            </th>
                            <th class="p-4 cursor-pointer select-none" @click="toggleSort('init_date')">
                                <div class="flex items-center gap-2">
                                    Fecha de inicio
                                    <SortIcon field="init_date" :current-field="sortField" :direction="sortDirection" />
                                </div>
                            </th>
                            <th class="p-4 cursor-pointer select-none" @click="toggleSort('finish_date')">
                                <div class="flex items-center gap-2">
                                    Fecha de finalización
                                    <SortIcon field="finish_date" :current-field="sortField" :direction="sortDirection" />
                                </div>
                            </th>
                            <th class="p-4 text-center cursor-pointer select-none" @click="toggleSort('results_count')">
                                <div class="flex items-center justify-center gap-2">
                                    Encuestados
                                    <SortIcon field="results_count" :current-field="sortField" :direction="sortDirection" />
                                </div>
                            </th>
                            <th class="p-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        <tr v-for="survey in filteredSurveys" :key="survey.id" class="text-slate-200 hover:bg-slate-600/30 transition-colors">
                            <td class="p-4 font-medium">{{ survey.name }}</td>
                            <td class="p-4">{{ formatedDate(survey.init_date) }}</td>
                            <td class="p-4">{{ formatedDate(survey.finish_date) }}</td>
                            <td class="p-4 text-center">{{ survey.results_count }}</td>
                            <td class="p-4">
                                <div class="flex gap-3 justify-center">
                                    <Link :href="`/surveys/details/${survey.id}`">
                                        <Icon class="text-xl text-blue-400 hover:text-blue-300 cursor-pointer" icon="ic:baseline-remove-red-eye"/>
                                    </Link>
                                    <Icon @click="idSurveyToEdit=survey.id; isModalOpen=true;" class="text-xl text-yellow-500 hover:text-yellow-400 cursor-pointer" icon="ic:baseline-edit"/>
                                    <Icon class="text-xl text-red-500 hover:text-red-400 cursor-pointer" icon="ic:baseline-restore-from-trash"/>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- modal para crear nueva encuesta -->
        <ImportSurveyModal
            :show="isImportModalOpen"
            @close="isImportModalOpen = false"
            @import-started="handleImportProcess"
        />
        <Modal :show="isModalOpen" @close="isModalOpen = false;">
            <SurveyForm :surveyId="idSurveyToEdit" />
        </Modal>
        <Pagination
            v-if="pagination"
            :pagination="pagination"
            @change="getSurveys"
        />
    </MainLayout>
</template>
<script setup>
import { Icon } from "@iconify/vue";
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

import SurveyForm from '@/components/forms/survey-form.vue';
import ImportSurveyModal from '@/components/ImportSurveyModal.vue';
import Modal from '@/components/modal.vue';
import Pagination from '@/components/pagination.vue';
import SortIcon from '@/components/sort-icon.vue';
import { getSurveysPaginated, importSurveyFromExcel } from '@/composables/api/surveys';
import { formatedDate } from '@/composables/shared';
import { useBatchProcessor } from '@/composables/useBatchProcessor';
import { useNotification } from '@/composables/useNotification';
import MainLayout from '@/layouts/main-layout.vue';

const { notify } = useNotification();
const { isProcessing, pollBatchStatus } = useBatchProcessor();
const isModalOpen = ref(false);
const isImportModalOpen = ref(false);
const surveys = ref([]);
const pagination = ref(null);

const idSurveyToEdit = ref(0);
const searchQuery = ref('');
const sortField = ref('name');
const sortDirection = ref('asc');
const sortableFields = [
    { key: 'name', label: 'Nombre' },
    { key: 'init_date', label: 'Inicio' },
    { key: 'finish_date', label: 'Fin' },
    { key: 'results_count', label: 'Respuestas' },
];

const toggleSort = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
};

onMounted(async () => {
        const params = new URLSearchParams(window.location.search);
    await getSurveys(parseInt(params.get('page')) || 1);
});

const filteredSurveys = computed(() => {
    const query = searchQuery.value.toLowerCase().trim();
    const filtered = surveys.value.filter(survey => {
        const nameMatch = survey.name.toLowerCase().includes(query);
        const initDateMatch = formatedDate(survey.init_date).toLowerCase().includes(query);
        const finishDateMatch = formatedDate(survey.finish_date).toLowerCase().includes(query);

        return nameMatch || initDateMatch || finishDateMatch;
    });

    return [...filtered].sort((a, b) => {
        let comparison = 0;

        if (sortField.value === 'name') {
            comparison = a.name.localeCompare(b.name);
        } else if (sortField.value === 'init_date') {
            comparison = new Date(a.init_date) - new Date(b.init_date);
        } else if (sortField.value === 'finish_date') {
            comparison = new Date(a.finish_date) - new Date(b.finish_date);
        } else if (sortField.value === 'results_count') {
            comparison = (a.results_count || 0) - (b.results_count || 0);
        }

        return sortDirection.value === 'asc' ? comparison : -comparison;
    });
});

const getSurveys = async (page = 1) => {
    router.get(window.location.pathname, { page }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });

    const result = await getSurveysPaginated(page);
    
    if (!result.errorFlag && result.data) {
        surveys.value = result.data.data;
        pagination.value = result.data;
    } else {
        notify(result.responseMessage || 'Error al cargar las encuestas', 'error');
    }
};

const importSurvey = () => {
    isImportModalOpen.value = true;
};

const handleImportProcess = async (formData) => {
    isImportModalOpen.value = false;
    isProcessing.value = true;

    const result = await importSurveyFromExcel(formData);

    if (!result.errorFlag && result.data) {
        await pollBatchStatus(result.data.batch_id);
        notify("Encuesta importada exitosamente");
        await getSurveys();
    } else {
        isProcessing.value = false;
        notify(result.responseMessage || "Error al importar la encuesta", 'error');
    }
};
</script>
<style scoped>
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #64748b #0f172a;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #0f172a;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #64748b;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>