<template>
    
    <NotificationBox v-if="message || isError? true:false" :message="message" :isError="isError" class="absolute z-10 right-0 top-0 w-100"/>

    <h1 class="text-3xl text-blue-100 font-extrabold text-center my-5">Resumen de encuestas</h1>
    <div class="w-full flex flex-wrap md:flex-nowrap items-center space-x-2">
        <!-- Listado de encuestas -->
        <div class="bg-white/30 backdrop-blur-md shadow-lg rounded-xl p-6 border border-blue-700/50 
        w-full sm:w-[75%] md:w-[55%] lg:w-[35%]
        h-125 overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30">
            <h2 class="underline text-white font-bold text-lg mb-4">Listado de encuestas</h2>
            <input 
                v-model="searchQuery" 
                type="text" 
                placeholder="Buscar encuesta..." 
                class="w-full mb-4 px-4 py-2 rounded-lg bg-blue-900/40 border border-blue-500 text-white placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-yellow-400"
            />
            <ul class="text-blue-100 mt-2">
                <li @click="callCategories(survey.id)" 
                    v-for="survey in filteredSurveys" 
                    :key="survey.id"
                    :class="[
                        'cursor-pointer py-2 px-3 rounded-md transition-colors duration-200',
                        surveySelected === survey.id 
                            ? 'bg-yellow-500/30 text-white border-l-4 border-yellow-400 font-bold' 
                            : 'hover:bg-blue-800/40 hover:text-yellow-400'
                    ]">
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
                        <li @click="callQuestions(category.id)" v-if="categories.length>0"
                            v-for="category in categories"
                            class="cursor-pointer py-1 px-2 rounded-md transition-colors duration-200"
                            :key="category.id"
                            :class="[
                                'cursor-pointer py-1 px-2 rounded-md transition-colors duration-200',
                                categorySelected === category.id
                                    ? 'bg-yellow-500/30 text-white border-l-4 border-yellow-400 font-bold'
                                    : 'hover:bg-blue-800/40 hover:text-yellow-400'
                            ]">
                            {{ category.name }}
                        </li>
                        <li v-else> No Hay Elementos</li>
                    </ul>
                </div>
                <div class="w-full lg:w-1/3
                overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30 h-115">
                    <h2 class="underline text-white font-bold text-lg">Preguntas</h2>
                    <ul class="text-blue-100 mt-2">
                        <li @click="callAnswers(question.id)" v-if="questions.length>0"
                            v-for="question in questions"
                            :key="question.id"
                            :class="[
                                'cursor-pointer py-1 px-2 rounded-md transition-colors duration-200',
                                questionSelected === question.id
                                    ? 'bg-yellow-500/30 text-white border-l-4 border-yellow-400 font-bold'
                                    : 'hover:bg-blue-800/40 hover:text-yellow-400'
                            ]">
                            {{ question.name }}
                        </li>
                        <li v-else> No Hay Elementos</li>
                    </ul>
                </div>
                <div class="w-full lg:w-1/3
                overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30 h-115">
                    <h2 class="underline text-white font-bold text-lg">Respuestas</h2>
                    <ul class="text-blue-100 mt-2">
                        <li v-if="answers.length>0" v-for="answer in answers" class="cursor-pointer hover:underline hover:text-yellow-400 py-1">
                            {{ answer.name }}
                        </li>
                        <li v-else> No Hay Elementos</li>
                    </ul>
                </div>
            </div> 
        </div>
    </div>
    
    
   
</template>
<script setup>
import { onMounted, ref, computed } from 'vue';
import {getSurveys,getCategoriesBySurvey} from '../composables/api/surveys'
import {getQuestionsByCategory} from '../composables/api/questions'
import { useAnswers} from '../composables/api/answers'
import NotificationBox from './notification-box.vue';

const {
    getAnswersByQuestion: getAnswersByQuestionApi
} = useAnswers()

const isError = ref(false)
const message = ref('')

const surveys = ref([])
const categories = ref([])
const questions = ref([])
const answers = ref([])

const surveySelected = ref(null)
const categorySelected = ref(null)
const questionSelected = ref(null)
const searchQuery = ref('')

const filteredSurveys = computed(() => {
    return surveys.value.filter(survey => 
        survey.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

    onMounted(async ()=>{
        try {
            const {data,errorFlag,responseMessage} = await getSurveys({})
            
            if(errorFlag){
                isError.value = errorFlag
                message.value = responseMessage
            }
            
            surveys.value = data.data   
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
        surveySelected.value = surveyId
        categorySelected.value = null
        questionSelected.value = null
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
       categorySelected.value = categoryId
       questionSelected.value = null
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
        questionSelected.value = questionId
        try {
            const {data,errorFlag,responseMessage} = await getAnswersByQuestionApi(questionId)
            console.log("data-answers", data )
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