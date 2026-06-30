<template>
    <Head :title="'Preguntas y respuestas'" />
    <MainLayout>
        <NotificationBox v-if="message || isError? true:false" :message="message" :is-error="isError" class="absolute z-10 right-0 top-0 w-100"/>
        <StepNavigation :items="steps" :current="current" />
        <div class="w-100 min-h-10 mx-auto my-3 flex flex-col ">
            
            <select name="" v-model="categorySelected" id="" class="inputs-form bg-white ">
                <option :value="0">Seleccione categoría</option>
                <option :value="category.id" class="p-2 text-neutral-800" v-for="category in categories"> {{ category.name }}</option>
                
            </select>
        </div>
        
        <div class="flex items-center justify-between w-full mb-3">
            <div class="w-60">
                <button type="button" @click="newQuestions()" class="text-white font-bold flex items-center justify-center gap-x-1 yellow-button-app cursor-pointer h-10" :disabled="!categorySelected">
                    Crear preguntas
                    <Icon class="text-2xl" icon="ic:outline-plus" />
                </button>
            </div>
            
            <div class="w-60">
                <button :disabled="!questionSelected" type="button" @click="newAnswers()" class="text-white font-bold flex items-center justify-center gap-x-1 yellow-button-app cursor-pointer h-10">
                    Asociar respuestas
                    <Icon class="text-2xl" icon="ic:outline-plus" />
                </button>
            </div>
            

        </div>
        <div class="flex space-x-2">
                
            <div class="bg-white/30 backdrop-blur-md shadow-lg rounded-xl p-6 border border-blue-700/50 
            w-1/2
            h-125">
                <h3 class="text-xl text-center text-white font-extrabold mb-3">Listado de preguntas</h3>
                <div id="table-header" class="h-10 w-full">
                    <table class="table-fixed w-full text-left">
                        <thead>
                            <tr class="border-b border-white/30 text-white text-lg">
                                <th class="w-30">Orden</th>
                                <th>Nombre</th>
                                <th class="w-55 text-center">Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div id="table-body" class="w-full max-h-90 overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30">
                    <table class="table-fixed w-full">
                        <tbody class="">
                            <tr :id="`question-${index}`" v-for="(question,index) in questions" class="text-white border-b border-neutral-400">
                                <td class="py-2 w-30">{{ question.order }}</td>
                                <td @click="questionSelected = question" class="py-2">
                                    
                                    {{ question.name }}
                                    
                                </td>
                                <td class="py-2 w-45">
                                    <div class="flex items-center justify-center gap-x-3 w-full">
                                        <Link :href="`/questions/details/${question.id}`">
                                            <Icon class="text-lg md:text-2xl text-blue-600 hover:text-blue-500 cursor-pointer" icon="ic:baseline-remove-red-eye"/>
                                        </Link>
                                        
                                        <Icon @click="getQuestionToEdit(question.id) " class="text-2xl text-yellow-600 hover:text-yellow-500 cursor-pointer" icon="ic:baseline-edit"/>
                                        <Icon class="text-lg md:text-2xl text-red-600 hover:text-red-500 cursor-pointer" icon="ic:baseline-restore-from-trash"/>
                                    </div>
                                </td>
                            </tr>
                            
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white/30 backdrop-blur-md shadow-lg rounded-xl p-6 border border-blue-700/50 
            w-1/2
            h-125">
                <h3 v-if="questionSelected" class="text-xl text-center text-white font-extrabold mb-3">Pregunta: {{ questionSelected.name }}</h3>
                <h3 v-if="!questionSelected" class="text-xl text-center text-white font-extrabold mb-3 underline">No ha seleccionado una pregunta.</h3>
                <h4 class="text-lg text-center text-white font-extrabold mb-3">Respuestas asociadas</h4>
                <div id="table-header" class="h-10 w-full">
                    <table class="table-fixed w-full text-left">
                        <thead>
                            <tr class="border-b border-white/30 text-white text-lg">
                                <th class="w-30">Orden</th>
                                <th>Nombre</th>
                                <th class="w-55 text-center">Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div id="table-body" class="w-full max-h-90 overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30">
                    <table class="table-fixed w-full">
                        <tbody class="">
                            <tr :id="`answer-${index}`" v-for="(answer,index) in answers" class="text-white border-b border-neutral-400">
                                <td class="py-2 w-30">{{ answer.order }}</td>
                                <td class="py-2">
                                    
                                    {{ answer.name }}
                                    
                                </td>
                                <td class="py-2 w-45">
                                    <div class="flex items-center justify-center gap-x-3 w-full">
                                        
                                        <Icon class="text-lg md:text-2xl text-blue-600 hover:text-blue-500 cursor-pointer" icon="ic:baseline-remove-red-eye"/>
                                        
                                        
                                        <Icon @click="getQuestionToEdit(question.id) " class="text-2xl text-yellow-600 hover:text-yellow-500 cursor-pointer" icon="ic:baseline-edit"/>
                                        <Icon class="text-lg md:text-2xl text-red-600 hover:text-red-500 cursor-pointer" icon="ic:baseline-restore-from-trash"/>
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
            
            
            <form @submit.prevent="operation_name =='Crear'?createManyQuestions():updateQuestion(questionSelected)" action="" class=" w-150 h-70">
                <div class="flex item-center justify-end space-x-3">
                    <button @click.prevent="incrementFormRow('question')" class="" v-if="operation_name!='Editar'">
                        <Icon class="h-8 w-8 p-1 rounded-full bg-yellow-400 cursor-pointer hover:bg-yellow-300 text-white " icon="ic:outline-plus" />
                    </button>
                    
                    <button type="submit" class="cursor-pointer">
                        <Icon class="h-8 w-8 bg-blue-600 hover:bg-blue-700 text-xs text-white p-1 rounded-full" icon="ic:round-save" />
                    </button>
                    
                </div>
                <div class="w-full h-full max-h-full overflow-y-scroll">
                    <div v-for="(formRow,index) in formQuestion" class="mb-3 ">
                        <div class="text-center font-bold mb-3">
                            <span>Pregunta {{ index+1 }}</span>
                        </div>
                        <div class="flex items-center justify-between space-x-2">
                            <div class="w-35 flex items-center space-x-2">
                                <label for="" class="text-sm font-bold">Orden: </label>
                                <input required v-model="formRow.order" min="1" type="number" class="inputs-form">
                            </div>

                            <div class="w-full flex items-center space-x-2">
                                <label for="" class="text-sm font-bold">Nombre: </label>
                                <input required minlength="5" v-model="formRow.name" type="text" class="inputs-form">
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
            
            
            <form @submit.prevent="operation_name =='Crear'?createManyAnswers_(formAnswer):updateAnswer(selectedAnswer?.id)" action="" class=" w-150 min-h-50 max-h-70 overflow-y-scroll">
                <div class="flex item-center justify-end space-x-3">
                <button @click.prevent="incrementFormRow('answer')" v-if="operation_name!='Editar'">
                    <Icon class="h-8 w-8 p-1 rounded-full bg-yellow-400 cursor-pointer hover:bg-yellow-300 text-white " icon="ic:outline-plus" />
                </button>
                
                <button type="submit" class="cursor-pointer">
                    <Icon class="h-8 w-8 bg-blue-600 hover:bg-blue-700 text-xs text-white p-1 rounded-full" icon="ic:round-save" />
                </button>
                
            </div>
                <div v-for="(formRow,index) in formAnswer" class="mb-3">
                    <div class="text-center font-bold mb-3">
                        <span>Respuesta {{ index+1 }}</span>
                    </div>
                    <div class="flex items-center justify-between space-x-2">
                        <div class="w-35 flex items-center space-x-2">
                            <label for="" class="text-sm font-bold">Orden: </label>
                            <input required v-model="formRow.order" min="1" type="number" class="inputs-form">
                        </div>

                        <div class="w-full flex items-center space-x-2">
                            <label for="" class="text-sm font-bold">Nombre: </label>
                            <input required minlength="5" v-model="formRow.name" type="text" class="inputs-form">
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
        <div class="mx-auto w-1/2 mb-3">
            <button class="green-button-app cursor-pointer" :disabled="nextStepFlag">
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

    
})

watch(surveySelected,async (value)=>{
    
    // router.get('/categories', {
    //     surveyId:value,
    //     //page: page.value,
    // }, {
    //     preserveState: true, // Evita que Vue destruya el estado del componente
    //     replace: true        // No satura el historial del botón "Atrás" del navegador
    // });

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
        
      const {} =  await createMany(formQuestion.value)

        await getQuestions(categorySelected.value);    
        
    } catch (error) {
        console.log(error)
    }
}

const newQuestions = ()=>{
    isQuestionModalOpen.value = true; operation_name.value = 'Crear'
    formQuestion.value[0].name = ''
    formQuestion.value[0].order = 0
}

const newAnswers = ()=>{
    isAnswerModalOpen.value = true; operation_name.value = 'Crear'
    formQuestion.value[0].name = ''
    formQuestion.value[0].order = 0
}

const createManyAnswers_ = async (formData)=>{
    const {status} = await createManyAnswers(formData)
    try {
        if(status==201){
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
    if (!survey?.categories) return false;

    return survey.categories.every(category => 
        category.questions?.every(question => 
            Array.isArray(question.answers) && question.answers.length >= 2
        ) ?? true
    );
};
</script>