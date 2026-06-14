<template>
    <div id="background-poll" class="dark:bg-gray-800 flex gap-y-3 h-screen items-center">
        <div class="max-w-7xl mx-auto bg-white/75 w-full md:w-10/12 h-full md:h-120 flex flex-col justify-around rounded-3xl pb-3">
            <h1 class="text-2xl font-bold mb-4 flex items-center justify-center bg-blue-900 text-white h-20 w-full rounded-t-3xl">{{ c?.name }}</h1>

            <!-- Questions and Answer Component -->
            <QuestionsAndAnswer class="mx-auto p-5 w-full max-w-11/12 h-full md:h-2/3 shadow-lg shadow-neutral-500" v-if="q" :question="q" :answers="a"/>
            
            <!-- Centered Buttons -->
            <div class="flex justify-around w-10/12  mt-8 mx-auto">
                <button
                    :disabled="disabledRewind"
                    type="button"
                    class="bg-green-700 text-white cursor-pointer rounded py-2 px-4 hover:bg-green-600 disabled:bg-gray-300 disabled:text-gray-600"
                    @click="decrementQuestion"
                >
                    Anterior
                </button>
                <button
                    :disabled="disabledForward"
                    type="button"
                    class="bg-blue-900 text-white cursor-pointer rounded py-2 px-4 hover:bg-blue-700 disabled:bg-gray-300 disabled:text-gray-600"
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

const q = ref([])
const a = ref([])
const c = ref()
const counts = ref({
    actual_category:0,
    actual_question:0,
    total_categories:0,
    total_questions:0
})
const disabledForward=ref(false)
const disabledRewind = ref (true)

const showPerson = async (id) => {
    try {
        const response = await axios.get(`/api/person/respondent/show/${id}`);
        console.log('Persona obtenida:', response.data);
    } catch (error) {
        console.error('Error al obtener persona:', error);
    }
};

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
    }
    
})

const incrementQuestion = () => {
    try {

        let categories_compare
        let questions_compare
        
        if(counts.value.actual_question  < counts.value.total_questions-1){
            counts.value.actual_question++    
            if(survey.value.categories[counts.value.actual_category]?.questions[counts.value.actual_question]?.answers){
                const {answers,...rest} = survey.value.categories[counts.value.actual_category].questions[counts.value.actual_question]
                if(rest)
                    q.value = rest
                if(answers)
                    a.value = answers
            }
            if(counts.value.actual_question>0 && counts.value.actual_category>=0)
                disabledRewind.value = false
        }else{
            if(counts.value.actual_question >= counts.value.total_questions-1){
                if(counts.value.actual_category < counts.value.total_categories){
                    counts.value.actual_category++
                    counts.value.actual_question = 0
                    q.value = []
                    a.value = []
                    if(survey.value.categories[counts.value.actual_category]){
                        counts.value.total_questions = survey.value.categories[counts.value.actual_category]?.question? survey.value.categories[counts.value.actual_category]?.question.length : 0
                        const {questions,...category} = survey.value.categories[counts.value.actual_category]
        
                        c.value = category
                        if(counts.value.total_questions.length>0){
                            
                            q.value = questions? questions[counts.value.actual_question] : []
                        
                            a.value = questions[counts.value.actual_question]?.answers ? questions[counts.value.actual_question]?.answers : []
                            
                        }
                    }
                }
                
            }
        }
        
        categories_compare = counts.value.actual_category == counts.value.total_categories-1
        questions_compare = counts.value.actual_question == counts.value.total_questions
        if(categories_compare && questions_compare)
            disabledForward.value = categories_compare
        
    } catch (error) {
        console.log({error})
    }
    
}

const decrementQuestion = () => {
    try {
        let categories_compare
        let questions_compare
        if(counts.value.actual_question > 0 && counts.value.actual_category>=0){
            counts.value.actual_question--
            const {answers,...rest} = survey.value.categories[counts.value.actual_category].questions[counts.value.actual_question]
            
            q.value = rest
            a.value = answers
        }else{
            if(counts.value.actual_question == 0 && counts.value.actual_category>0){
               
            counts.value.actual_category--

                const {questions} = c.value = survey.value.categories[counts.value.actual_category]
                
                counts.value.total_questions = questions.length
                counts.value.actual_question = counts.value.total_questions-1 > 0 ? counts.value.total_questions-1 : 0
                q.value = questions[counts.value.actual_question]
                if(q.value?.answers){
                    const {answers} = q.value
                    a.value = answers
                }
            }
        }

        disabledRewind.value = !counts.value.actual_category && !counts.value.actual_question?true:false
        categories_compare = counts.value.actual_category <= counts.value.total_categories
        questions_compare = counts.value.actual_question < counts.value.total_questions
        if(categories_compare && questions_compare)
            disabledForward.value = false
            
    } catch (error) {
        console.log({error})
    }
    
}
</script>

<style scoped>
    
/* You can add your custom CSS here if needed */
</style>