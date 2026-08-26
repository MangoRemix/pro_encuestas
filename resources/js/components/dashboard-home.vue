<template>
    <NotificationBox v-if="message" :message="message" :isError="isError" class="fixed z-50 right-4 top-4"/>



    <div class="max-w-7xl mx-auto p-4 md:p-0">
        <h1 class="text-2xl md:text-3xl text-slate-100 font-bold mb-6 md:mb-8 text-center">Resumen de encuestas</h1>


        <div class="flex flex-col lg:flex-row gap-4 h-auto lg:h-150">
        <!-- Listado de encuestas -->


            <div class="bg-gray-500/50 border border-slate-700 rounded-lg p-3 w-full lg:w-80 flex flex-col">
                <h2 class="text-white font-semibold mb-3 text-xs uppercase tracking-wider">Encuestas</h2>
            <input 
                v-model="searchQuery" 
                type="text" 
                    placeholder="Buscar..."

                    class="w-full mb-4 px-3 py-2 rounded bg-slate-900 border border-slate-700 text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 transition-all"
            />
                <div class="flex-1 overflow-y-auto pr-2 space-y-1 custom-scrollbar">
                    <button
                    v-for="survey in filteredSurveys" 
                    :key="survey.id"
                        @click="callCategories(survey.id)"
                    :class="[

                            'w-full text-left px-3 py-2 rounded text-sm transition-colors',
                        surveySelected === survey.id 


                                ? 'bg-slate-600 text-white'
                                : 'text-slate-300 hover:bg-slate-700'
                    ]">
                    {{ survey.name }}
                    </button>
                </div>
        </div>

            <!-- Panel de Datos -->
            <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Categorías -->


                <div class="bg-gray-500/50 border border-slate-700 rounded-lg p-4 flex flex-col">
                    <h2 class="text-white font-medium mb-3 text-sm">Categorías</h2>
                    <div class="flex-1 overflow-y-auto custom-scrollbar space-y-1">
                        <button v-for="cat in categories" :key="cat.id" @click="callQuestions(cat.id)"

                            :class="['w-full text-left px-3 py-2 rounded text-sm transition-colors', categorySelected === cat.id ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700']">
                            {{ cat.name }}
                        </button>
                </div>
                </div>

                <!-- Preguntas -->


                <div class="bg-gray-500/50 border border-slate-700 rounded-lg p-4 flex flex-col">
                    <h2 class="text-white font-medium mb-3 text-sm">Preguntas</h2>
                    <div class="flex-1 overflow-y-auto custom-scrollbar space-y-1">
                        <button v-for="q in questions" :key="q.id" @click="callAnswers(q.id)"

                            :class="['w-full text-left px-3 py-2 rounded text-sm transition-colors', questionSelected === q.id ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700']">
                            {{ q.name }}
                        </button>
                </div>
            </div> 

                <!-- Respuestas -->


                <div class="bg-gray-500/50 border border-slate-700 rounded-lg p-4 flex flex-col">
                    <h2 class="text-white font-medium mb-3 text-sm">Respuestas</h2>
                    <div class="flex-1 overflow-y-auto custom-scrollbar space-y-1">

                        <div v-for="ans in answers" :key="ans.id" class="px-3 py-2 text-sm text-slate-300 border-b border-slate-700/50">
                            {{ ans.name }}
        </div>
    </div>
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
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }


.custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #64748b; }
</style>