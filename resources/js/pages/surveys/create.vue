<script setup>
import SurveyForm from '@/components/survey-form.vue';
import { onMounted } from 'vue';
onMounted(()=>{
    document.title = 'Encuestas: crear-nueva'
})
</script>
<template>
    <div>
        <SurveyForm/>
    </div>
</template>