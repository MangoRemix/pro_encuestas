<template>
    <Head title="Encuestas: detalles" />
    <MainLayout>
        <div class=" text-center">
            <h2 class="text-3xl text-white font-bold mt-8">Encuestas</h2>
        </div>
        <div class="w-11/12 flex justify-end mb-3">
            <button class="flex  items-center rounded-full text-white bg-yellow-400 cursor-pointer hover:bg-yellow-300 h-9 w-40 p-2 font-bold">
                <Link href="/surveys/create" class="flex items-center">
                    <Icon class="text-2xl " icon="ic:outline-plus" /> 
                    Crear encuesta
                </Link>
            </button>
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
                            <th class="text-sm p-2 lg:text-lg w-fit lg:w-55 text-center">Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div id="table-body" class="w-full max-h-100 overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30">
                <table class="table-fixed w-full">
                    <tbody class="">
                        <tr  v-for="survey in surveys" class="text-white border-b border-neutral-400">
                            <td class="py-2 w-70 lg:w-105">
                                {{ survey.name }}
                            </td>
                            <td class="py-2 w-fit lg:w-50">
                                {{ formatedDate(survey.init_date) }}
                            </td>
                            <td class="py-2 w-fit lg:w-50">
                                {{ formatedDate(survey.finish_date) }}
                            </td>
                            <td class="py-2 w-25 lg:w-55">
                                <div class="w-full flex gap-x-1 lg:gap-x-3 justify-center align-center">
                                    <Link :href="`/categories`" :data="{surveyId:survey.id}">
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