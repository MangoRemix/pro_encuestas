<script setup>

import SurveyForm from '@/components/forms/survey-form.vue';
import MainLayout from '@/layouts/main-layout.vue';
import { onMounted } from 'vue';
onMounted(()=>{
    document.title = 'Encuestas: crear-nueva'
})
</script>
<template>
    <MainLayout>
        <SurveyForm/>
    </MainLayout>
</template>