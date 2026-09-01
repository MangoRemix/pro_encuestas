<template>
    <Head :title="'Preguntas y respuestas'" />
    <MainLayout>
        <NotificationBox v-if="message" :message="message" :is-error="isError" class="absolute z-10 right-0 top-0 w-100"/>
        <StepNavigation :items="steps" :current="current" />
        <div class="w-full md:w-100 min-h-10 my-3 flex flex-col">
            
            <select name="" v-model="categorySelected" id="" class="inputs-form bg-white w-full">
                <option :value="0">Seleccione categoría</option>
                <option :value="category.id" :key="category.id" class="p-2 text-neutral-800" v-for="category in categories"> {{ category.name }}</option>
                
            </select>
        </div>
        
        <div class="flex flex-col sm:flex-row items-center justify-between w-full mb-3 gap-y-3 sm:gap-y-0">
            <div class="w-full sm:w-55">
                <button type="button" @click="newQuestions()" class="w-full text-white font-bold flex items-center justify-center gap-x-2 yellow-button-app cursor-pointer" :disabled="!categorySelected">
                    <Icon class="text-2xl" icon="ic:outline-plus" />
                    Crear preguntas
                    
                </button>
            </div>
            
            <div class="w-full sm:w-60">
                <button :disabled="!questionSelected" type="button" @click="newAnswers()" class="w-full text-white font-bold flex items-center justify-center gap-x-2 yellow-button-app cursor-pointer">
                    <Icon class="text-2xl" icon="ic:outline-plus" />
                    Asociar respuestas
                </button>
            </div>
            

        </div>
        <div class="flex flex-col md:flex-row gap-y-4 md:gap-y-0 md:space-x-2">
                
            <div class="bg-gray-500/50 border border-slate-700 rounded-lg overflow-hidden w-full md:w-1/2 h-125">
                <h3 class="text-xl text-center text-white font-extrabold mb-3 mt-3">Listado de preguntas</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-700 bg-slate-900/50 text-white text-xs uppercase tracking-wider">
                                <th class="p-2 sm:p-4 w-12 sm:w-20">Orden</th>
                                <th class="p-2 sm:p-4 w-full">Nombre</th>
                                <th class="p-2 sm:p-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50 custom-scrollbar max-h-90 overflow-y-scroll">
                            <tr :id="`question-${index}`" v-for="(question,index) in questions" :key="question.id" @click="questionSelected = question" 
                            :class="['text-slate-200 transition-colors cursor-pointer w-full', questionSelected?.id === question.id ? 'bg-yellow-500 text-white font-semibold' : 'hover:bg-slate-600/30']"
                            >
                                <td class="p-2 sm:p-4 w-12 sm:w-20">{{ question.order }}</td>
                                <td class="p-2 sm:p-4 w-full wrap-break-word">
                                    {{ question.name }}
                                </td>
                                <td class="p-2 sm:p-4">
                                    <div class="flex items-center justify-center gap-x-2 sm:gap-x-3">
                                        <Link :href="`/questions/details/${question.id}`">
                                            <Icon class="text-xl text-blue-400 hover:text-blue-300 cursor-pointer" icon="ic:baseline-remove-red-eye"/>
                                        </Link>
                                        
                                        <Icon @click.stop="getQuestionToEdit(question.id)" class="text-xl text-yellow-500 hover:text-yellow-400 cursor-pointer" icon="ic:baseline-edit"/>
                                        <Icon class="text-xl text-red-500 hover:text-red-400 cursor-pointer" icon="ic:baseline-restore-from-trash"/>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-gray-500/50 border border-slate-700 rounded-lg overflow-hidden w-full md:w-1/2 h-125" v-if="questionSelected">
                <!-- <h3 v-if="questionSelected" class="text-xl text-center text-white font-extrabold mb-3 mt-3">{{ questionSelected.name }}</h3> -->
                <!--<h3 v-if="!questionSelected" class="text-xl text-center text-white font-extrabold mb-3 mt-3 underline">No ha seleccionado una pregunta.</h3>-->
                <h4 class="text-xl text-center text-white font-extrabold mb-3 mt-3">Respuestas asociadas</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-700 bg-slate-900/50 text-white text-xs uppercase tracking-wider">
                                <th class="p-2 sm:p-4 w-12 sm:w-20">Orden</th>
                                <th class="p-2 sm:p-4 w-full">Nombre</th>
                                <th class="p-2 sm:p-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50 custom-scrollbar max-h-90 overflow-y-scroll">
                            <tr :id="`answer-${index}`" v-for="(answer,index) in answers" :key="answer.id" class="text-slate-200 hover:bg-slate-600/30 transition-colors w-full">
                                <td class="p-2 sm:p-4 w-12 sm:w-20">{{ answer.order }}</td>
                                <td class="p-2 sm:p-4 w-full wrap-break-word">
                                    {{ answer.name }}
                                </td>
                                <td class="p-2 sm:p-4">
                                    <div class="flex items-center justify-center gap-x-2 sm:gap-x-3">
                                        <Icon class="text-xl text-blue-400 hover:text-blue-300 cursor-pointer" icon="ic:baseline-remove-red-eye"/>
                                        <Icon @click="getQuestionToEdit(question.id)" class="text-xl text-yellow-500 hover:text-yellow-400 cursor-pointer" icon="ic:baseline-edit"/>
                                        <Icon class="text-xl text-red-500 hover:text-red-400 cursor-pointer" icon="ic:baseline-restore-from-trash"/>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <Modal :show="isQuestionModalOpen" @close="isQuestionModalOpen = false">
            <!-- FORMULARIO CATEGORIES -->
            
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">
                {{ operation_name }} preguntas
            </h2>
            
            
            <form @submit.prevent="operation_name =='Crear'?createManyQuestions():updateQuestion(questionSelected)" action="" class="w-full md:w-150 h-70">
                <div class="flex item-center justify-end space-x-3">
                    <button @click.prevent="incrementFormRow('question')" class="" v-if="operation_name!='Editar'">
                        <Icon class="h-8 w-8 p-1 rounded-full bg-yellow-400 cursor-pointer hover:bg-yellow-300 text-white " icon="ic:outline-plus" />
                    </button>
                    
                    <button type="submit" class="cursor-pointer">
                        <Icon class="h-8 w-8 bg-blue-600 hover:bg-blue-700 text-xs text-white p-1 rounded-full" icon="ic:round-save" />
                    </button>
                    
                </div>
                <div class="w-full h-full max-h-full overflow-y-scroll">
                    <div v-for="(formRow,index) in formQuestion" :key="index" class="mb-3 ">
                        <div class="text-center font-bold mb-3">
                            <span>Pregunta {{ index+1 }}</span>
                        </div>
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-y-3 sm:gap-y-0 sm:space-x-2">
                            <div class="w-full sm:w-35 flex flex-col sm:flex-row sm:items-center gap-y-1 sm:space-x-2">
                                <label for="" class="text-sm font-bold">Orden: </label>
                                <input required v-model="formRow.order" min="1" type="number" class="inputs-form w-full sm:w-auto">
                            </div>

                            <div class="w-full flex flex-col sm:flex-row sm:items-center gap-y-1 sm:space-x-2">
                                <label for="" class="text-sm font-bold">Nombre: </label>
                                <input required minlength="5" v-model="formRow.name" type="text" class="inputs-form w-full">
                            </div>
                        </div>
                        
                    </div>
                </div>
                
            </form>
        </Modal>
        <!--Modal Respuestas-->
        <Modal :show="isAnswerModalOpen" @close="isAnswerModalOpen = false">
            <!-- FORMULARIO RESPUESTAS -->
            
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">
                {{ operation_name }} respuestas
            </h2>
            
            
            <form @submit.prevent="operation_name =='Crear'?createManyAnswers_(formAnswer):updateAnswer(selectedAnswer?.id)" action="" class="w-full md:w-150 min-h-50 max-h-70 overflow-y-scroll">
                <div class="flex item-center justify-end space-x-3">
                <button @click.prevent="incrementFormRow('answer')" v-if="operation_name!='Editar'">
                    <Icon class="h-8 w-8 p-1 rounded-full bg-yellow-400 cursor-pointer hover:bg-yellow-300 text-white " icon="ic:outline-plus" />
                </button>
                
                <button type="submit" class="cursor-pointer">
                    <Icon class="h-8 w-8 bg-blue-600 hover:bg-blue-700 text-xs text-white p-1 rounded-full" icon="ic:round-save" />
                </button>
                
            </div>
                <div v-for="(formRow,index) in formAnswer" :key="index" class="mb-3">
                    <div class="text-center font-bold mb-3">
                        <span>Respuesta {{ index+1 }}</span>
                    </div>
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-y-3 sm:gap-y-0 sm:space-x-2">
                        <div class="w-full sm:w-35 flex flex-col sm:flex-row sm:items-center gap-y-1 sm:space-x-2">
                            <label for="" class="text-sm font-bold">Orden: </label>
                            <input required v-model="formRow.order" min="1" type="number" class="inputs-form w-full sm:w-auto">
                        </div>

                        <div class="w-full flex flex-col sm:flex-row sm:items-center gap-y-1 sm:space-x-2">
                            <label for="" class="text-sm font-bold">Nombre: </label>
                            <input required minlength="5" v-model="formRow.name" type="text" class="inputs-form w-full">
                        </div>
                    </div>
                    
                </div>
                
            </form>
        </Modal>

        <div class="text-white">
            <b>Nota:</b> <span>Para finalizar la carga: </span>
            
                
            <ol class="list-decimal ml-10" >
                <li class="underline underline-offset-4">
                    <b>TODAS</b> las categorías deben tener preguntas asignadas <b>MÍNIMO 1</b>
                </li>
                <li class="underline underline-offset-4">
                    <b>TODAS</b> las preguntas deben tener respuestas asignadas <b>MÍNIMO 2</b>
                </li>
            </ol>
                
        </div>
        <div class="mx-auto w-full sm:w-1/2 md:w-1/4 lg:w-1/4 mb-3 flex justify-center">
            <button @click="NextStep()" class="w-full green-button-app cursor-pointer" :disabled="nextStepFlag">
                Finalizar
            </button>    
        </div>

            

    </MainLayout>
</template>
<script setup>
import NotificationBox from '@/components/notification-box.vue';
import Modal from '@/components/modal.vue';
import MainLayout from '@/layouts/main-layout.vue';
import StepNavigation from '@/components/StepNavigation.vue';

import { onMounted, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

import { createMany, getQuestion, getQuestionsByCategory } from '@/composables/api/questions';
import { getCategoriesBySurvey, getSurveys, showFullSurvey } from '@/composables/api/surveys';
import { getAnswersByQuestion,updateAnswer,createManyAnswers } from '@/composables/api/answers';
import { currentStep, stepsBreadcrumb } from '@/store/store';

const operation_name = ref('create')
const isQuestionModalOpen = ref(false)
const isAnswerModalOpen = ref(false)

const questions = ref([])
const categories = ref([])
const answers = ref([])
const page = usePage()
const categorySelected = ref(0)
const surveySelected = ref(0)
const questionSelected = ref(null)
const selectedAnswer = ref()

const steps = stepsBreadcrumb
const current = currentStep
const nextStepFlag = ref(true)

const formAnswer = ref([{
    name:'',
    order:0,
    question_id:parseInt(questionSelected.value?.id)
}])
const formQuestion = ref([
    {
        name:'',
        order:0,
        category_id:parseInt(page.props.categoryId)
    }
])

const message = ref()
const isError = ref(false)

onMounted(async()=>{
   
    current.value = 'Preguntas'

    setTimeout(() => {
        if(page.props.categoryId){
            surveySelected.value = page.props.surveyId
            categorySelected.value = parseInt(page.props.categoryId)
        }
        else{
            if(page.props.surveyId)
                surveySelected.value = page.props.surveyId
        }
    }, 750);   
    nextStepFlag.value = !(await validateQuestionsAnswers())
    
})

watch(surveySelected,async (value)=>{

    const {data,errorFlag,responseMessage} = await getCategoriesBySurvey(value)
    
    if(data){
        
        categories.value = data
        
    }
    else{
        if(errorFlag){
            isError.value = true
            message.value = responseMessage
            setTimeout(() => {
                message.value = ''
            }, 3500);
        }
    }
})

watch(categorySelected,async (value)=>{
    
    questionSelected.value = null
    answers.value = null
    router.get('/surveys/create-survey/step-3', {
        surveyId:surveySelected.value,
        categoryId:value
        //page: page.value,
    }, {
        preserveState: true, // Evita que Vue destruya el estado del componente
        replace: true        // No satura el historial del botón "Atrás" del navegador
    });

    await getQuestions(categorySelected.value)

    
    nextStepFlag.value = !(await validateQuestionsAnswers())
})

watch(questionSelected,async (value) => {
    if(value?.id){
        const {data,errorFlag,responseMessage} = await getAnswersByQuestion(value.id)
        answers.value = data
        formAnswer.value = [{
            order:0,
            name:'',
            question_id:value.id
        }]
    }
    
})
const getQuestions = async (value)=>{
    
    const {data,errorFlag,responseMessage} = await getQuestionsByCategory(value)
    
    if(data){
        
        questions.value = data
        
        formQuestion.value = [
            {
                name:'',
                order:0,
                category_id:parseInt(page.props.categoryId)
            }
        ]
    }
    else{
        if(errorFlag){
            isError.value = true
            message.value = responseMessage
            setTimeout(() => {
                message.value = ''
            }, 3500);
        }
    }
}

function incrementFormRow (type) {
    console.log("increment", type)
    if(type == 'question'){
        formQuestion.value.push({
            name:'',
            order:0,
            category_id:parseInt(page.props.categoryId)
        })
    }else{
        formAnswer.value.push({
            name:'',
            order:0,
            question_id:parseInt(questionSelected.value?.id)
        })
    }
    
}

const getQuestionToEdit = async (id) => {
    try {
        const {data,errorFlag,responseMessage} = await getQuestion(id)
        
        if(data){
            
            formQuestion.value[0].name = data.name
            formQuestion.value[0].order = data.order
            isQuestionModalOpen.value = true
            operation_name.value = 'Editar'
            questionSelected.value = data
        }

        if(errorFlag){
            isError.value = true
            message.value = responseMessage
            setTimeout(() => {
                message.value = ''
            }, 3500);
        }
        
    } catch (error) {
        console.log(error)
    }
}

const createManyQuestions = async () => {
    try {
        const { errorFlag } = await createMany(formQuestion.value)
        
        isError.value = errorFlag
        message.value = errorFlag ? 'Error al cargar pregunta(s)' : 'Pregunta guardada exitosamente'

        setTimeout(() => {
            message.value = ''
        }, 3000)
        await getQuestions(categorySelected.value)
        
    } catch (error) {
        console.log(error)
    }
}

const newQuestions = ()=>{
    isQuestionModalOpen.value = true; operation_name.value = 'Crear'
    formQuestion.value = [{
        name:'',
        order:0,
        category_id:parseInt(page.props.categoryId)
    }]
}

const newAnswers = ()=>{
    isAnswerModalOpen.value = true; operation_name.value = 'Crear'
    formAnswer.value = [{
        name:'',
        order:0,
        question_id:parseInt(questionSelected.value?.id)
    }]
}

const createManyAnswers_ = async (formData)=>{
    const {success} = await createManyAnswers(formData)
    
    try {
        if(success){
            const {data} = await getAnswersByQuestion(questionSelected.value.id)

            answers.value = data
        }       
    } catch (error) {
        
    }
    
    nextStepFlag.value = !(await validateQuestionsAnswers())
}

const updateAnswers = async (id)=>{
    await updateAnswer(id,formAnswer.value)
}

const validateQuestionsAnswers = async () => {
    const {data:survey} = await showFullSurvey(page.props.surveyId)
    console.log(survey)
    if (!survey?.categories || survey.categories.length === 0) return false;

    return survey.categories.every(category => {
        // Obliga a que haya mínimo 1 pregunta
        if (!category.questions || category.questions.length === 0) return false;
        
        // Y mínimo 2 respuestas por pregunta
        return category.questions.every(question => 
            Array.isArray(question.answers) && question.answers.length >= 2
        );
    });
};

const NextStep = () =>{
    router.get('/surveys/create-survey/step-4',{
        surveyId:page.props.surveyId
    })
}
</script>
<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #64748b; }
</style>

