<template>
    
    <NotificationBox v-if="message || isError? true:false" :message="message" :isError="isError" class="absolute z-10 right-0 top-0 w-100"/>

    <h1 class="text-3xl text-blue-100 font-extrabold text-center my-5">Resumen de encuestas</h1>
    <div class="w-full flex flex-wrap md:flex-nowrap items-center space-x-2">
        <!-- Listado de encuestas -->
        <div class="bg-white/30 backdrop-blur-md shadow-lg rounded-xl p-6 border border-blue-700/50 
        w-full sm:w-[75%] md:w-[55%] lg:w-[35%]
        h-125 overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30">
            <h2 class="underline text-white font-bold text-lg">Listado de encuestas</h2>
            <ul class="text-blue-100 mt-2">
                <li @click="callCategories(survey.id)" v-for="survey in surveys" class="cursor-pointer hover:underline hover:text-yellow-400 py-1">
                    {{ survey.name }}
                </li>
            </ul>
        </div>

        <!-- Listado Categorías -->
        <div class="bg-white/30 backdrop-blur-md shadow-lg rounded-xl p-6 border border-blue-700/50 
        w-full h-125">
            <div class="w-full flex flex-wrap md:flex-nowrap space-x-2">
                <div class="w-full lg:w-1/3
                overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30 h-115">
                    <h2 class="underline text-white font-bold text-lg">Categorías</h2>
                    <ul class="text-blue-100 mt-2">
                        <li @click="callQuestions(category.id)" v-for="category in categories" class="cursor-pointer hover:underline hover:text-yellow-400 py-1">
                            {{ category.name }}
                        </li>
                    </ul>
                </div>
                <div class="w-full lg:w-1/3
                overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30 h-115">
                    <h2 class="underline text-white font-bold text-lg">Preguntas</h2>
                    <ul class="text-blue-100 mt-2">
                        <li @click="callAnswers(question.id)" v-for="question in questions" class="cursor-pointer hover:underline hover:text-yellow-400 py-1">
                            {{ question.name }}
                        </li>
                    </ul>
                </div>
                <div class="w-full lg:w-1/3
                overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30 h-115">
                    <h2 class="underline text-white font-bold text-lg">Respuestas</h2>
                    <ul class="text-blue-100 mt-2">
                        <li v-for="answer in answers" class="cursor-pointer hover:underline hover:text-yellow-400 py-1">
                            {{ answer.name }}
                        </li>
                    </ul>
                </div>
            </div> 
        </div>
    </div>
    
    
   
</template>
<script setup>
import { onMounted,ref } from 'vue';
import {getSurveys,getCategoriesBySurvey} from '../composables/api/surveys'
import {getQuestionsByCategory} from '../composables/api/questions'
import {getAnswersByQuestion} from '../composables/api/answers'
import NotificationBox from './notification-box.vue';

const isError = ref(false)
const message = ref('')

const surveys = ref([])
const categories = ref([])
const questions = ref([])
const answers = ref([])

    onMounted(async ()=>{
        try {
            const {data,errorFlag,responseMessage} = await getSurveys()
            if(errorFlag){
                isError.value = errorFlag
                message.value = responseMessage
            }
            surveys.value = data    
        } catch (error) {
            console.log("error",{error})
        }finally{
            setTimeout(() => {
                message.value=''
            }, 3500);
        }
        
    })

    const callCategories = async (surveyId) => {
        categories.value = []
        questions.value = []
        answers.value = []
        try {
            const {data,errorFlag,responseMessage} = await getCategoriesBySurvey(surveyId)
            if(errorFlag){
                isError.value = errorFlag
                message.value = responseMessage
            }
            categories.value = data    
        } catch (error) {
            console.log("error",{error})
        }finally{
            setTimeout(() => {
                message.value=''
            }, 3500);
        }
    }

    const callQuestions = async (categoryId) => {
       questions.value = []
       answers.value = []
        try {
            const {data,errorFlag,responseMessage} = await getQuestionsByCategory(categoryId)
            if(errorFlag){
                isError.value = errorFlag
                message.value = responseMessage
            }
            questions.value = data    
        } catch (error) {
            console.log("error",{error})
        }finally{
            setTimeout(() => {
                message.value=''
            }, 3500);
        }

    }

    const callAnswers = async(questionId) => {
        answers.value = []
        try {
            const {data,errorFlag,responseMessage} = await getAnswersByQuestion (questionId)
            if(errorFlag){
                isError.value = errorFlag
                message.value = responseMessage
            }
            answers.value = data    
        } catch (error) {
            console.log("error",{error})
        }finally{
            setTimeout(() => {
                message.value=''
            }, 3500);
        }
    }
</script>
<style scoped>
    
</style>