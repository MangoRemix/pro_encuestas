<template>
    <Head title="Encuestas: detalles" />
    <div class=" text-center">
        <h2 class="text-3xl text-blue-600 font-bold">Encuestas</h2>
    </div>
    <div class="w-full h-100 overflow-scroll">
        <table class="table-fixed w-10/12 mx-auto mt-8">
            <thead class="bg-blue-900 text-white text-left">
                <tr>
                    <th class="p-2 text-lg w-120">Nombre</th>
                    <th class="p-2 text-lg w-70">Fecha de inicio</th>
                    <th class="p-2 text-lg w-70">Fecha de finalización</th>
                    <th class="p-2 text-lg w-70 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="survey in surveys">
                    <td class="p-2 border border-neutral-300 w-120">{{ survey.name }}</td>
                    <td class="p-2 border border-neutral-300">{{ survey.init_date }}</td>
                    <td class="p-2 border border-neutral-300">{{ survey.finish_date }}</td>
                    <td class="p-2 border border-neutral-300">
                        <div class="w-full flex gap-x-5 justify-center align-center">
                            <Icon class="text-2xl text-blue-600 hover:text-blue-500 cursor-pointer" icon="ic:baseline-remove-red-eye"/>
                            <Icon class="text-2xl text-yellow-600 hover:text-yellow-500 cursor-pointer" icon="ic:baseline-edit"/>
                            <Icon class="text-2xl text-red-600 hover:text-red-500 cursor-pointer" icon="ic:baseline-restore-from-trash"/>
                        </div>
                        
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script setup>
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { Icon } from "@iconify/vue";
import {apiHost} from '../../store/store'
import { onMounted, ref } from 'vue';

const surveys = ref([])

onMounted(async ()=>{
    surveys.value = await getSurveys()
})

const getSurveys = async () => {
    try {
        const response = await axios.get(`${apiHost}survey/show-all`)
        console.log(response)
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