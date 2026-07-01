
<template>
    <Head title="Nueva encuesta: paso 1" />
    <MainLayout>
        <div class="w-200 flex items-center mx-auto">
            <StepNavigation :items="steps" :current="current" />
        </div>
        
        <SurveyForm/>
    </MainLayout>
</template>
<script setup>

import SurveyForm from '@/components/forms/survey-form.vue';
import StepNavigation from '@/components/StepNavigation.vue';
import MainLayout from '@/layouts/main-layout.vue';
import { currentStep, stepsBreadcrumb } from '@/store/store';
import { Head } from '@inertiajs/vue3';
import { onMounted } from 'vue';
const steps = stepsBreadcrumb
const current = currentStep;



onMounted(()=>{
    
    current.value = 'General'
    document.title = 'Encuestas: crear-nueva'
})
</script>