<template>
    <Head title="Encuestado en curso"/>
    <div id="background-poll" class="dark:bg-gray-800 flex gap-y-3 min-h-screen items-center px-5">
        <SuccessModal :show="showSuccess" />
        <div class="max-w-7xl mx-auto bg-white w-full md:w-10/12 flex flex-col justify-between rounded-3xl h-[95vh] overflow-hidden">

            <!-- Barra de Progreso -->
            <div class="w-full px-8 pt-6 mb-2 shrink-0">
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

            <!-- Questions and Answer Component -->
            <h1 class="text-lg md:text-2xl font-bold mb-4 flex items-center justify-center bg-blue-900 text-white h-20 w-full shrink-0">{{ c?.name }}</h1>
            <!-- Questions and Answer Component -->
            <div class="grow h-60 md:px-2 md:h-85">
            <QuestionsAndAnswer
            :key="q.id"
            @send-answer="getAnswer"
                class="w-full" v-if="q" :question="q" :answers="a"/>
            </div>

            <!-- Centered Buttons -->
            <div class="flex space-x-3 md:space-x-0 justify-around w-full py-6 px-8 mt-auto shrink-0 ">
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
import SuccessModal from '@/components/SuccessModal.vue';

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
    person_id: 0,
    question_id: 0,
    answer_id: 0,
    pollster_id: page.props.auth.user.id
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
        await storageResults();
        selectedAnswer.value = null;






        const currentCat = survey.value.categories[counts.value.actual_category];
        const totalQuestionsInCat = currentCat.questions.length;





































        // Si hay más preguntas en la categoría actual
        if (counts.value.actual_question < totalQuestionsInCat - 1) {
            counts.value.actual_question++;
        }












        // Si se acabó la categoría actual, pasar a la siguiente
        else if (counts.value.actual_category < survey.value.categories.length - 1) {
            counts.value.actual_category++;
            counts.value.actual_question = 0;
        }
        
        // Actualizar datos de la pregunta actual
        const nextCat = survey.value.categories[counts.value.actual_category];
        const nextQ = nextCat.questions[counts.value.actual_question];

        c.value = { ...nextCat, questions: undefined }; // Evitar pasar todo el array
        q.value = { ...nextQ, answers: undefined };
        a.value = nextQ.answers;

        disabledRewind.value = false;

        // Verificar si es la última pregunta de la última categoría
        const isLastCategory = counts.value.actual_category === survey.value.categories.length - 1;
        const isLastQuestion = counts.value.actual_question === survey.value.categories[counts.value.actual_category].questions.length - 1;

        visibilityFinishButton.value = isLastCategory && isLastQuestion;
    } catch (error) {

        console.error(error);
    }

}

const decrementQuestion = () => {
    try {























        if (counts.value.actual_question > 0) {
            counts.value.actual_question--;
        } else if (counts.value.actual_category > 0) {
            counts.value.actual_category--;
            counts.value.actual_question = survey.value.categories[counts.value.actual_category].questions.length - 1;
        }
                
        const prevCat = survey.value.categories[counts.value.actual_category];
        const prevQ = prevCat.questions[counts.value.actual_question];


        c.value = { ...prevCat, questions: undefined };
        q.value = { ...prevQ, answers: undefined };
        a.value = prevQ.answers;








        disabledRewind.value = (counts.value.actual_category === 0 && counts.value.actual_question === 0);
        visibilityFinishButton.value = false;
    } catch (error) {

        console.error(error);
    }

}

const storageResults = ()=>{
    
    let historial = JSON.parse(localStorage.getItem('miHistorialData')) || [];

    const index = historial.findIndex(item => item.question_id === result.value.question_id);

    if (index !== -1) {
        historial[index] = { ...result.value };
    } else {
        historial.push({ ...result.value });
    }
    
    localStorage.setItem('miHistorialData', JSON.stringify(historial));
}

const finishSurvey = async () => {
    await storageResults();

    const historial = JSON.parse(localStorage.getItem('miHistorialData')) || [];

    if (historial.length === 0) return;

    const allSurveys = JSON.parse(localStorage.getItem('allSurveysPending')) || [];
    const surveyToSave = {
        data: historial,
        status: 'PENDIENTE',
        survey:survey.value,
        created_at: new Date().toISOString()
    };
    allSurveys.push(surveyToSave);
    localStorage.setItem('allSurveysPending', JSON.stringify(allSurveys));

    try {
        //await axios.post('/api/result/batch', { results: historial });
        localStorage.removeItem('miHistorialData');

        showSuccess.value = true;
        setTimeout(() => {
            showSuccess.value = false;
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
    result.value.question_id = parseInt(page.props.question)
    result.value.person_id = parseInt(page.props.userId)
    
})
</script>

<style scoped>
    
/* You can add your custom CSS here if needed */
</style>