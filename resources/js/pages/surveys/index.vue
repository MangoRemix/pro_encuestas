<template>
    <Head title="Encuestas: detalles" />
    <MainLayout>
        <div class=" text-center">
            <h2 class="text-3xl text-white font-bold mt-8">Encuestas</h2>
        </div>
        <div class="w-11/12 flex justify-end">
            <button @click="isModalOpen = true" class="">
                <Icon class="h-9 w-9 p-1 rounded-full text-white bg-yellow-400 cursor-pointer hover:bg-yellow-300" icon="ic:outline-plus" />
            </button>
        </div>
        <!-- tabla listado de encuestas -->
        <div class="w-full h-100 mt-8">
            <div id="table-header"  class="w-full md:w-11/12 mx-auto">
                <table class="table-auto w-full">
                    <thead class="bg-blue-900 text-white text-left">
                        <tr class="w-full">
                            <th class="text-sm p-2 md:text-lg w-120">Nombre</th>
                            <th class="text-sm p-2 md:text-lg w-50">Fecha de inicio</th>
                            <th class="text-sm p-2 md:text-lg w-50 lg:text-nowrap">Fecha de finalización</th>
                            <th class="text-sm p-2 md:text-lg w-55 text-center">Acciones</th>
                        </tr>
                    </thead>
                </table>    
            </div>
            <div id="table-body" class="w-full md:w-11/12 max-h-80 overflow-y-scroll mx-auto">
                <table class="table-auto w-full">
                    <tbody class="bg-white">
                        <tr v-for="survey in surveys">
                            <td class="text-xs md:text-lg p-2 border border-neutral-300 w-120 hover:underline">
                                <Link :href="`/surveys/details/${survey.id}`">
                                    {{ survey.name }}
                                </Link>
                            </td>
                            <td class="text-xs md:text-lg p-2 border border-neutral-300 w-50">{{ formatedDate(survey.init_date) }}</td>
                            <td class="text-xs md:text-lg p-2 border border-neutral-300 w-50">{{ formatedDate(survey.finish_date) }}</td>
                            <td class="text-xs md:text-lg p-2 border border-neutral-300 w-50">
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
        <Modal :show="isModalOpen" @close="isModalOpen = false;">
            <SurveyForm :surveyId="idSurveyToEdit" />
        </Modal>
    </MainLayout>
</template>
<script setup>
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { Icon } from "@iconify/vue";
import {apiHost} from '../../store/store'
import { onMounted, ref } from 'vue';

import MainLayout from '@/layouts/main-layout.vue';
import Modal from '@/components/modal.vue';

import { formatedDate } from '@/composables/shared';
import SurveyForm from '@/components/forms/survey-form.vue';

const isModalOpen = ref(false);
const surveys = ref([])
const idSurveyToEdit = ref(0)

onMounted(async ()=>{
    surveys.value = await getSurveys()
})

const getSurveys = async () => {
    try {
        const response = await axios.get(`${apiHost}survey/show-all`)
        
        if(response.data.length > 0)
            return response.data
        else
            return 'No hay encuestas registradas.'
    } catch (error) {
        console.log(error)   
    }
}
    
</script>
<style lang="">
    
</style>