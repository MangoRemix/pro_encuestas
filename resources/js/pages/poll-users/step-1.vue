<template>
    <Head title="Encuesta paso 1"/>
    <MainLayout>
        <div class="min-w-80 max-w-130 mx-auto">
            <select v-model="survey_selected" class="inputs-form bg-white" name="" id="">
                <option v-for="survey in surveys" :value="survey.id">{{ survey.name }}</option>
            </select>
        </div>
        <div class="w-80 mx-auto">
            <button @click="redirectToStep2()"
                class="primary-button-app w-full cursor-pointer"
            >
                Iniciar
            </button>
        </div>
    </MainLayout>
</template>
<script setup>
import { getSurveys } from '@/composables/api/surveys';
import MainLayout from '@/layouts/main-layout.vue';
import { Head, router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

    const surveys = ref([])
    const survey_selected = ref()
    onMounted(async ()=>{
        const {data} = await getSurveys()
        if(data)
            surveys.value = data
    })

    function redirectToStep2(){
        router.get(`/poll-users/step-2`,{
            surveyId:survey_selected.value
        })
    }
</script>
<style scoped>
    
</style>