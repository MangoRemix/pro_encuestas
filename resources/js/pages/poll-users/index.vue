<template>
    <div id="background-poll" class="dark:bg-gray-800 flex gap-y-3 h-screen items-center">
<div class="max-w-7xl mx-auto p-1 md:p-4 bg-white/65 w-full md:w-10/12 h-full md:h-120 flex flex-col justify-around rounded-3xl shadow-lg transition duration-300 ease-in-out hover:shadow-xl">
            <h1 class="text-2xl font-bold mb-4 text-center">{{ c?.name }}</h1>

            <!-- Questions and Answer Component -->
            <QuestionsAndAnswer class="mx-auto p-5 w-full md:w-9/12 max-w-11/12 h-full md:h-2/3" v-if="q" :question="q" :answers="a"/>
            
            <!-- Centered Buttons -->
            <div class="flex justify-around w-10/12  mt-8 mx-auto">
                <button
                    type="button"
                    class="bg-green-500 text-white rounded py-2 px-4 hover:bg-green-700 disabled:bg-gray-300 disabled:text-gray-600"
                    @click="decrementQuestion"
                >
                    Anterior
                </button>
                <button
                    type="button"
                    class="bg-blue-500 text-white rounded py-2 px-4 hover:bg-blue-700 disabled:bg-gray-300 disabled:text-gray-600"
                    @click="incrementQuestion"
                >
                    Siguiente
                </button>
        </div>

    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import QuestionsAndAnswer from '@/components/poll/QuestionsAndAnswer.vue';

const survey = ref(null);

const q = ref()
const a = ref([])
const c = ref()
const counts = ref({
    actual_category:0,
    actual_question:0,
    total_categories:0,
    total_questions:0
})

onMounted(async () => {
    try {
        const response = await axios.get('/api/survey/show-full/2');
        survey.value = response.data;
        
    } catch (error) {
        console.error("Error cargando la encuesta completa:", error);
    }
});
watch(survey, (value)=>{
    
    if(value.categories.length>0){
        counts.value.total_categories = value.categories.length

        const {questions,...category} = value.categories[0]
        const {answers,...rest} = questions[0]

        counts.value.total_questions = questions.length

        q.value = rest
        a.value = answers
        c.value = category
        console.log(q.value,a.value,c.value)
    }
    
})

const incrementQuestion = () => {
    try {
        counts.value.actual_question += 1
        if(counts.value.actual_question<=counts.value.total_questions){
            const {answers,...rest} = survey.value.categories[counts.value.actual_category].questions[counts.value.actual_question]
        
            q.value = rest
            a.value = answers
        }

        if(counts.value.actual_question>counts.value.total_questions){
            counts.value.actual_question = 0
            counts.value.actual_category += 1
            if(counts.value.actual_category <= counts.value.total_categories){
                const {questions,...category} = survey.value.categories[counts.value.actual_category].questions[counts.value.actual_question]
                q.value = rest
                c.value = category
                a.value = answers
            }

        }
    } catch (error) {
        console.log({error})
    }
    
}

const decrementQuestion = () => {
    try {
        counts.value.actual_question -= 1
        if(counts.value.actual_question >= 0){
            const {answers,...rest} = survey.value.categories[counts.value.actual_category].questions[counts.value.actual_question]
        
            q.value = rest
            a.value = answers
        }

        if(counts.value.actual_question < 0){
            counts.value.actual_question = counts.value.total_questions - 1
            if(counts.value.actual_category >= 0){
                const {questions,...category} = survey.value.categories[counts.value.actual_category].questions[counts.value.actual_question]
                q.value = rest
                c.value = category
                a.value = answers
            }

        }
    } catch (error) {
        console.log({error})
    }
    
}
</script>

<style scoped>
    #background-poll{
        background-image: url('/public/images/vanishing-stripes1.svg');
    }
/* You can add your custom CSS here if needed */
</style>