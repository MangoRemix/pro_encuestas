<template>
    <Head title="Encuesta paso 1"/>
    <MainLayout>
        <div class="min-w-80 max-w-130 mx-auto">
            <h3 class="mx-auto text-white text-2xl underline font-bold text-center mb-2">Seleccione encuesta a realizar.</h3>
            <select v-model="survey_selected" class="inputs-form bg-white" name="" id="">
                <option v-for="survey in surveys" :value="survey.id">{{ survey.name }}</option>
            </select>
        </div>
        <div class="w-80 mx-auto">
            <button @click="redirectToStep2()"
                class="primary-button-app w-full cursor-pointer"
                :disabled="!survey_selected"
            >
                Iniciar
            </button>
        </div>
    </MainLayout>
</template>
<script setup>
import { getSurveys } from '@/composables/api/surveys';
import MainLayout from '@/layouts/main-layout.vue';
import { apiHost } from '@/store/store';
import { Head, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref } from 'vue';

    const surveys = ref([])
    const survey_selected = ref()
    onMounted(async ()=>{
        const {data} = await getSurveys()
        if(data)
            surveys.value = data
    })

    async function redirectToStep2  (){
        await preCreatePerson()
        // router.get(`/poll-users/step-2`,{
        //     surveyId:survey_selected.value
        // })
    }

    const preCreatePerson = async ()=>{
        console.log(survey_selected.value)
        try {
            const response = await axios.get(`${apiHost}person/respondent/pre-create`)
                router.get('/poll-users/step-2',{
                id:response.data.id,
                surveyId:survey_selected.value
            })
            console.log(response.data.id)
            return response.data
        } catch (error) {
            console.error(error)
        }
    }
</script>
<style scoped>
    
</style>