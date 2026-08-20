<template>
    <Head title="Encuestas: detalles" />
    <MainLayout>
        <div class=" text-center">
            <h2 class="text-3xl text-white font-bold mt-8 underline">Encuestas</h2>
        </div>
        <div class="w-full flex justify-between items-center mb-3">
            <div class="w-1/3 ">
                <input v-model="searchQuery" type="text" class="inputs-form bg-white">
            </div>
            <div class="flex gap-2">
                <button
                    @click="importSurvey"
                    :disabled="isProcessing"
                    class="flex items-center rounded-full text-white bg-green-600 cursor-pointer hover:bg-green-500 h-9 px-4 font-bold disabled:opacity-50"
                >
                    <span v-if="isProcessing" class="animate-spin mr-2 border-2 border-white border-t-transparent rounded-full w-4 h-4"></span>
                    <Icon v-else class="text-xl mr-1" icon="ic:outline-file-upload" />
                    {{ isProcessing ? 'Importando...' : 'Importar Excel' }}
                </button>
                <button class="flex  items-center rounded-full text-white bg-yellow-400 cursor-pointer hover:bg-yellow-300 h-9 w-40 p-2 font-bold">
                    <Link href="/surveys/create-survey/step-1" class="flex items-center">
                        <Icon class="text-2xl " icon="ic:outline-plus" />
                        Crear encuesta
                    </Link>
                </button>
            </div>
        </div>

        <!-- Nueva tabla -->

        <div class="bg-white/30 backdrop-blur-md shadow-lg rounded-xl p-6 border border-blue-700/50 
            w-full h-135">
            
            <div id="table-header" class="h-10 w-full mb-3">
                <table class="table-fixed w-full text-left">
                    <thead>
                        <tr class="border-b border-white/30 text-white text-lg">
                            <th class="text-sm p-2 lg:text-lg w-70 lg:w-100">Nombre</th>
                            <th class="text-sm p-2 lg:text-lg w-fit lg:w-50">Fecha de inicio</th>
                            <th class="text-sm p-2 lg:text-lg w-fit lg:w-50 text-nowrap">Fecha de finalización</th>
                            <th class="text-sm p-2 lg:text-lg w-fit lg:w-30">Encuestados</th>
                            <th class="text-sm p-2 lg:text-lg w-fit lg:w-55 text-center">Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div id="table-body" class="w-full max-h-100 overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30">
                <table class="table-fixed w-full">
                    <tbody class="">
                        <tr  v-for="survey in filteredSurveys" class="text-white border-b border-neutral-400">
                            <td class="py-2 w-70 lg:w-105">
                                {{ survey.name }}
                            </td>
                            <td class="py-2 w-fit lg:w-50">
                                {{ formatedDate(survey.init_date) }}
                            </td>
                            <td class="py-2 w-fit lg:w-50">
                                {{ formatedDate(survey.finish_date) }}
                            </td>
                            <td class="py-2 w-fit lg:w-20 text-center">
                                {{ survey.results_count }}
                            </td>
                            <td class="py-2 w-25 lg:w-55 text-center">
                                <div class="w-full flex gap-x-1 lg:gap-x-3 justify-center align-center">
                                    <Link :href="`/surveys/details/${survey.id}`">
                                        <Icon class="text-lg md:text-2xl text-blue-600 hover:text-blue-500 cursor-pointer" icon="ic:baseline-remove-red-eye"/>
                                    </Link>
                                    
                                    <Icon @click="idSurveyToEdit=survey.id; isModalOpen=true;" class="text-lg md:text-2xl text-yellow-600 hover:text-yellow-500 cursor-pointer" icon="ic:baseline-edit"/>
                                    <Icon class="text-lg md:text-2xl text-red-600 hover:text-red-500 cursor-pointer" icon="ic:baseline-restore-from-trash"/>
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
            :current-page="pagination.current_page"
            :last-page="pagination.last_page"
            :total="pagination.total"
            :from="pagination.from"
            :to="pagination.to"
            @page-change="getSurveys"
        />
    </MainLayout>
</template>
<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import { Icon } from "@iconify/vue";
import {apiHost} from '../../store/store'
import { computed, onMounted, ref } from 'vue';

import MainLayout from '@/layouts/main-layout.vue';
import Modal from '@/components/modal.vue';
import Pagination from '@/components/pagination.vue';
import ImportSurveyModal from '@/components/ImportSurveyModal.vue';
import { formatedDate } from '@/composables/shared';
import SurveyForm from '@/components/forms/survey-form.vue';
import { useNotification } from '@/composables/useNotification';
import { importSurveyFromExcel } from '@/composables/api/surveys';
import { useBatchProcessor } from '@/composables/useBatchProcessor';

const { notify } = useNotification();
const { isProcessing, pollBatchStatus } = useBatchProcessor();
const isModalOpen = ref(false);
const isImportModalOpen = ref(false);
const surveys = ref([])
const pagination = ref(null)

const idSurveyToEdit = ref(0)
const searchQuery = ref('')
    
onMounted(async () => {
        const params = new URLSearchParams(window.location.search);
    await getSurveys(parseInt(params.get('page')) || 1);
})

const filteredSurveys = computed(() => {
    return surveys.value.filter(survey => 
        survey.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const getSurveys = async (page = 1) => {
    router.get(window.location.pathname, { page }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });

    try {
        const response = await axios.get(`${apiHost}survey/show-all`,{ 
            params:{ page }
        })
        
        surveys.value = response.data.data
        pagination.value = response.data
    } catch (error) {
        console.log(error)   
    }
}   

const importSurvey = () => {
    isImportModalOpen.value = true;
}

const handleImportProcess = async (formData) => {
    isImportModalOpen.value = false;
    isProcessing.value = true;

    try {
        const { data } = await axios.post(`${apiHost}survey/import-excel`, formData);

        await pollBatchStatus(data.batch_id);

            notify("Encuesta importada exitosamente");
        await getSurveys();
    } catch (error) {
        isProcessing.value = false;
        console.error("Error en la importación:", error);
        notify("Error al importar la encuesta", 'error');
    }
};
</script>
<style lang="">
    
</style>