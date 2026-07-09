<template>
    <Head title="Encuestado en curso"/>
    <div id="background-poll" class="dark:bg-gray-800 flex gap-y-3 min-h-screen items-center">
        <SuccessModal :show="showSuccess" />
        <div class="max-w-7xl mx-auto bg-white w-full md:w-10/12 flex flex-col justify-between rounded-3xl pb-3 pt-0 md:min-h-120">

            <!-- Barra de Progreso -->
            <div class="w-full px-8 pt-6 mb-2">
                <div class="flex justify-between text-sm text-gray-500 mb-1">
                    <span>Progreso</span>
                    <span>{{ currentQuestionIndex }} / {{ totalQuestionsCount }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-blue-900 h-2.5 rounded-full transition-all duration-500"
                        :style="{ width: `${(currentQuestionIndex / totalQuestionsCount) * 100}%` }">
                    </div>
                </div>
            </div>

            <h1 class="text-lg md:text-2xl font-bold mb-4 flex items-center justify-center bg-blue-900 text-white h-20 w-full rounded-t-3xl mt-0">{{ c?.name }}</h1>

            <!-- Questions and Answer Component -->
            <QuestionsAndAnswer
            :key="q.id"
            @send-answer="getAnswer"
            class="mx-auto p-5 w-full max-w-11/12 h-full md:h-2/3 shadow-lg shadow-neutral-500" v-if="q" :question="q" :answers="a"/>
            
            <!-- Centered Buttons -->
            <div class="flex space-x-3 md:space-x-0 justify-around w-10/12  mt-8 mx-auto">
                <button
                    :disabled="disabledRewind"
                    type="button"
                    class=" text-white cursor-pointer rounded py-2 px-4 yellow-button-app basis-xs"
                    @click="decrementQuestion"
                >
                    Anterior
                </button>
                <button
                    v-if="!visibilityFinishButton"
                    :disabled="!disabledRewind && !selectedAnswer"
                    type="button"
                    class=" text-white cursor-pointer rounded py-2 px-4 primary-button-app basis-xs"
                    @click="incrementQuestion"
                >
                    Siguiente
                </button>

                <button
                    v-if="visibilityFinishButton"
                    type="button"
                    class=" text-white cursor-pointer rounded py-2 px-4 green-button-app basis-xs"
                    @click="finishSurvey"
                    :disabled="!selectedAnswer"
                >
                    Finalizar
                </button>
            </div>

        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, watch, computed } from 'vue';
import {Head, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import QuestionsAndAnswer from '@/components/poll/QuestionsAndAnswer.vue';
import SuccessModal from '@/Components/SuccessModal.vue';

const page = usePage()

const survey = ref(null);
const showSuccess = ref(false);

const q = ref([])
const a = ref([])
const c = ref()
const selectedAnswer = ref()
const counts = ref({
    actual_category:0,
    actual_question:0,
    total_categories:0,
    total_questions:0
})

const result = ref({
    person_id:0,
    questions_id:0,
    answer_id:0
})

const disabledForward=ref(false)
const disabledRewind = ref (true)

const visibilityFinishButton = ref(false)

const totalQuestionsCount = computed(() => {
    if (!survey.value) return 0;
    return survey.value.categories.reduce((acc, cat) => acc + (cat.questions?.length || 0), 0);
});

const currentQuestionIndex = computed(() => {
    if (!survey.value) return 0;
    let index = 0;
    for (let i = 0; i < counts.value.actual_category; i++) {
        index += survey.value.categories[i].questions?.length || 0;
    }
    return index + counts.value.actual_question + 1;
});

const showPerson = async (id) => {
    try {
        const response = await axios.get(`/api/person/respondent/show/${id}`);
        
    } catch (error) {
        console.error('Error al obtener persona:', error);
    }
};

onMounted(async () => {
    try {
        const response = await axios.get(`/api/survey/show-full/${page.props.id}`);
        
        survey.value = response.data;
        
    } catch (error) {
        console.error("Error cargando la encuesta completa:", error);
    }

    
});
watch(survey, async (value)=>{
    
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

watch(q,(value)=>{
    
    router.get(`/poll-users/step-3/${page.props.userId}/survey/${page.props.id}`,{
        category: c.value.id ,
        question: value?.id,
    }, {preserveState:true})
})

const incrementQuestion = async () => {
    try {

        let categories_compare
        let questions_compare
        selectedAnswer.value = null
        await storageResults()
        if(counts.value.actual_question  < counts.value.total_questions-1){

            counts.value.actual_question++    
            
            if(survey.value.categories[counts.value.actual_category]?.questions[counts.value.actual_question]?.answers){
                const {answers,...rest} = survey.value.categories[counts.value.actual_category].questions[counts.value.actual_question]
                if(rest)
                    q.value = rest
                if(answers){
                    a.value = answers
                    
                }
            }
            
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
                        
                        if(questions && questions.length>0){
                            
                            q.value = questions[counts.value.actual_question]
                            
                            a.value = questions[counts.value.actual_question]?.answers ? questions[counts.value.actual_question]?.answers : []
                            
                        }
                    }
                }
                
            }
        }
        
        
        disabledRewind.value = false

        categories_compare = counts.value.actual_category == counts.value.total_categories-1
        questions_compare = counts.value.actual_question == counts.value.total_questions
        if(categories_compare && questions_compare){
            disabledForward.value = categories_compare
            visibilityFinishButton.value = categories_compare
        }
        
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
        if(categories_compare && questions_compare){
            disabledForward.value = false
            visibilityFinishButton.value = false
        }
            
    } catch (error) {
        console.log({error})
    }
    
}

const storageResults = ()=>{
    
    let historial = JSON.parse(localStorage.getItem('miHistorialData')) || [];

    const index = historial.findIndex(item => item.questions_id === result.value.questions_id);

    if (index !== -1) {
        historial[index] = { ...result.value };
    } else {
        historial.push({ ...result.value });
    }
    
    localStorage.setItem('miHistorialData', JSON.stringify(historial));
}

const finishSurvey = async () => {
    // Aseguramos guardar el último resultado antes de enviar
    await storageResults();

    const historial = JSON.parse(localStorage.getItem('miHistorialData')) || [];

    if (historial.length === 0) return;

    try {
        await axios.post('/api/result/batch', { results: historial });
        localStorage.removeItem('miHistorialData');

        showSuccess.value = true;
        setTimeout(() => {
            showSuccess.value = false;
            // router.visit('/poll-users/finished');
            router.visit('/poll-users/step-1')
        }, 2000);
        
    } catch (error) {
        console.error("Error al finalizar la encuesta:", error);
    }
}

const getAnswer = (answer)=>{

    selectedAnswer.value = answer
}
watch(selectedAnswer,(value)=>{
    
    result.value.answer_id = value
    result.value.questions_id = parseInt(page.props.question)
    result.value.person_id = parseInt(page.props.userId)
    
})
</script>

<style scoped>
    
/* You can add your custom CSS here if needed */
</style>