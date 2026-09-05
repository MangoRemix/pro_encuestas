<template>
    <Head :title="`Encuesta: ${survey.name}`" />
    <MainLayout>
        <NotificationBox v-if="message || isError? true:false" :message="message" :is-error="isError" class="absolute z-10 right-0 top-0 w-100"/>

        <div class="w-full text-center">
            <h1 class="text-white underline text-2xl font-bold mx-auto mb-2">{{ survey.name }}</h1>
        </div>
        
        
        <div class="flex items-center justify-between w-full mb-3">
            <div class="space-x-2 text-xl">
                <span class="text-white font-bold">Total encuestados:</span>
            <span class="text-white">{{ survey.results_count }}</span>
            </div>
        </div>
        <div class="flex space-x-2">
            <div class="bg-slate-600/50 backdrop-blur-md shadow-lg rounded-xl p-6 border border-blue-700/50 
            w-full sm:w-[75%] md:w-[55%] lg:w-[35%]
            h-125 overflow-y-auto custom-scrollbar">
                
                <div class="w-full flex items-center justify-between mb-3">
                    <h3 class="text-lg xl:text-xl text-center text-white font-extrabold ">Categorías</h3>
                    <div class="">
                    <button @click="isModalOpen_categories = true" class="btn-circle btn-circle-yellow text-white w-10 h-10 cursor-pointer"
                    >
                        <Icon class="text-2xl" icon="ic:outline-plus" />
                    </button>
                </div>
                </div>
                
                <ul class="text-blue-100 mt-2 text-md">
                    <li @click="categorySelected = category.id" v-for="category in categories" 
                    :class="`cursor-pointer hover:underline hover:text-yellow-400 hover:font-bold transition-all duration-75 py-1
                    ${categorySelected==category.id?'text-yellow-400':''}
                    `">
                        {{ category.name }}
                    </li>
                </ul>
            </div>
                
            <div class="bg-slate-600/50 backdrop-blur-md shadow-lg rounded-xl p-3 border border-blue-700/50 
            w-full
            h-125 flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xl text-center text-white font-extrabold">Preguntas</h3>
                <div class="">
                    <button @click="newQuestions()" class="btn-circle btn-circle-yellow text-white w-10 h-10 cursor-pointer"
                    :disabled="!categorySelected"
                    >
                        <Icon class="text-2xl" icon="ic:outline-plus" />                        
                        
                    </button>
                </div>
            </div>
                
                
                <div class="bg-gray-500/50 border border-slate-700 rounded-lg overflow-hidden flex-1 flex flex-col min-h-0">
                    <table class="w-full text-left border-collapse shrink-0 table-fixed">
                        <thead class="sticky top-0 z-10 bg-slate-900">
                            <tr class="border-b border-slate-700 bg-slate-900 text-white text-xs uppercase tracking-wider">
                                <th class="p-4 w-30">Orden</th>
                                <th class="p-4">Nombre</th>
                                <th class="p-4 w-55 text-center">Acciones</th>
                            </tr>
                        </thead>
                    </table>
                    <div class="overflow-y-auto custom-scrollbar flex-1 w-full">
                        <table class="w-full text-left border-collapse table-fixed">
                            <tbody class="divide-y divide-slate-700/50">
                                <tr :id="`question-${index}`" v-for="(question,index) in questions" :key="question.id" class="text-slate-200 hover:bg-slate-600/30 transition-colors">
                                    <td class="p-4 w-30 font-medium">{{ question.order }}</td>
                                    <td class="p-4 truncate">
                                        <span @click="questionSelected=question.id"
                                        :class="`cursor-pointer ${questionSelected==question.id?'text-yellow-400 font-bold':''}`"
                                        >
                                            {{ question.name }}
                                        </span>
                                    </td>
                                    <td class="p-4 w-55">
                                        <div class="flex items-center justify-center gap-x-3 w-full">
                                            <Link :href="`/questions/details/${question.id}`">
                                                <Icon class="text-xl text-blue-400 hover:text-blue-300 cursor-pointer" icon="ic:baseline-remove-red-eye"/>
                                            </Link>
                                            
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
        </div>

        <!-- aquí irán las respuestas -->

        <div class="bg-slate-600/50 backdrop-blur-md shadow-lg rounded-xl p-3 border border-blue-700/50 w-full mt-10 flex flex-col h-100">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xl text-center text-white font-extrabold">Respuestas</h3>
                <div class="">
                    <button @click="newAnswers()" class="cursor-pointer btn-circle btn-circle-yellow h-10 w-10"
                    :disabled="!questionSelected">
                        <Icon class="text-2xl" icon="ic:outline-plus" />
                    </button>
                </div>
                
            </div>
                
                <div class="bg-gray-500/50 border border-slate-700 rounded-lg overflow-hidden flex-1 flex flex-col min-h-0">
                    <table class="w-full text-left border-collapse shrink-0 table-fixed">
                        <thead>
                            <tr class="border-b border-slate-700 bg-slate-900 text-white text-xs uppercase tracking-wider">
                                <th class="p-4 w-30">Orden</th>
                                <th class="p-4">Nombre</th>
                                <th class="p-4 w-55 text-center">Acciones</th>
                            </tr>
                        </thead>
                    </table>
                    <div class="overflow-y-auto custom-scrollbar flex-1 w-full">
                        <table class="w-full text-left border-collapse table-fixed">
                            <tbody class="divide-y divide-slate-700/50 text-slate-200">
                                <tr class="hover:bg-slate-600/30 transition-colors" v-for="(answer,index) in answersByQuestion" :key="answer.id">
                                    <td class="p-4 w-30 font-medium">{{ answer.order }}</td>
                                    <td class="p-4 truncate">{{ answer.name }}</td>
                                    <td class="p-4 w-55">
                                        <div class="flex items-center justify-center gap-x-3 w-full">
                                            <Icon @click="getAnswerToEdit(answer.id)" class="text-xl text-yellow-500 hover:text-yellow-400 cursor-pointer" icon="ic:baseline-edit"/>
                                            <Icon @click="deleteAnswer(answer.id,index)" class="text-xl text-red-500 hover:text-red-400 cursor-pointer" icon="ic:baseline-restore-from-trash"/>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <!-- aqui iran los modales -->
        <Modal :show="isModalOpen" @close="isModalOpen = false">
            <!-- FORMULARIO QUESTIONS -->
            
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">
                {{ operation_name }} preguntas
            </h2>
            
            
            <form @submit.prevent="operation_name =='Crear'?createManyQuestions():updateQuestion(questionSelected)" action="" class=" w-150 h-70">
                <div class="flex item-center justify-end space-x-3">
                    <button @click.prevent="incrementFormRow" class="" v-if="operation_name!='Editar'">
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
        
        <!-- MODAL PARA RESPUESTAS -->
         <Modal :show="isModalOpen_answers" @close="isModalOpen_answers = false">
                <!-- FORMULARIO RESPUESTAS -->
                
                <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">
                    {{ operation_name }} respuestas
                </h2>
                
                
                <form @submit.prevent="operation_name =='Crear'?createManyAnswers():updateAnswer(answerSelectedId)" action="" class=" w-150 min-h-50 max-h-70 overflow-y-scroll">
                    <div class="flex item-center justify-end space-x-3">
                    <button @click.prevent="incrementFormRow_answer" class="" v-if="operation_name!='Editar'">
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

            <!-- MODAL PARA CREAR CATEGORIES -->
             <Modal :show="isModalOpen_categories" @close="isModalOpen_categories = false">
                <div class="min-w-150 max-w-2xl mx-auto">
                    <CategoryForm :survey_id="page.props.id" @update-categories="updateCategories" />
                </div>
             </Modal>

    </MainLayout>
</template>
<script setup>
import MainLayout from '@/layouts/main-layout.vue';
import NotificationBox from '@/components/notification-box.vue';
import Modal from '@/components/modal.vue';
import CategoryForm from '@/components/forms/category-form.vue';

import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { apiHost } from '@/store/store';

import { Icon } from '@iconify/vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

import { useAnswers } from '@/composables/api/answers';
import { useNotification } from '@/composables/useNotification';
import { createMany, getQuestion, getQuestionsByCategory } from '@/composables/api/questions';
import { getCategoriesBySurvey, getSurvey } from '@/composables/api/surveys';


const { message, isError, notify } = useNotification();
const {
    loading: loadingAnswers,
    getAnswersByQuestion: getAnswersByQuestionApi,
    deleteAnswer: deleteAnswerApi,
    createManyAnswers: createManyAnswersApi,
    updateAnswer: updateAnswerApi
} = useAnswers();

const operation_name = ref('create')
const isModalOpen = ref(false)
const isModalOpen_answers = ref(false)
const isModalOpen_categories = ref(false)
const questions = ref([])
const categories = ref([])
const survey = ref([])
const page = usePage()
const categorySelected = ref(0)
const surveySelected = ref(0)
const questionSelected = ref(0)
const answerSelectedId = ref(0)
const answersByQuestion = ref([])

const formQuestion = ref([
    {
        name:'',
        order:0,
        category_id:parseInt(page.props.categoryId)
    }
])

const formAnswer = ref([
    {
        name:'',
        order:0,
        question_id:0
    }
])

onMounted(async()=>{

    const {data} = await getSurvey(page.props.id)
    
    setTimeout(() => {
        if(page.props.categoryId){
            surveySelected.value = page.props.id
            categorySelected.value = parseInt(page.props.categoryId)
        }
        else if(page.props.id){
                surveySelected.value = page.props.id
        }
    }, 750);   

    if(data)
        survey.value = data
})

watch(surveySelected,async (value)=>{
    
    router.get(`/surveys/details/${page.props.id}`, {
    }, {
        preserveState: true, // Evita que Vue destruya el estado del componente
        replace: true        // No satura el historial del botón "Atrás" del navegador
    });

    await updateCategories()
})
watch(categorySelected,async (value)=>{

    router.get(`/surveys/details/${page.props.id}`, {
        categoryId:value
        //page: page.value,
    }, {
        preserveState: true, // Evita que Vue destruya el estado del componente
        replace: true        // No satura el historial del botón "Atrás" del navegador
    });

    const {data,errorFlag,responseMessage} = await getQuestionsByCategory(value)

    if(data){
        
        questions.value = data
        answersByQuestion.value = []
    }
    else if(errorFlag){
        notify(responseMessage, true);
    }
})
const incrementFormRow = () =>{
    formQuestion.value.push({
        name:'',
        order:0,
        category_id:parseInt(page.props.categoryId)
    })
}

const getQuestionToEdit = async (id) => {
        const {data,errorFlag,responseMessage} = await getQuestion(id)
        if(data){
            
            formQuestion.value[0].name = data.name
            formQuestion.value[0].order = data.order
            isModalOpen.value = true
            operation_name.value = 'Editar'
            questionSelected.value = id
    } else if(errorFlag) notify(responseMessage, true);
        }

const createManyQuestions = async () => {
    const {data,errorFlag,responseMessage} = await createMany(formQuestion.value)
            console.log(data)
        if(data){
        const {data: questions_} = await getQuestionsByCategory(page.props.categoryId)
        questions.value = questions_
        notify(data);
        formQuestion.value = [
                {
                    name:'',
                    order:0,
                    category_id:parseInt(page.props.categoryId)
                }
            ]

    } else if(errorFlag) notify(responseMessage, true);
        }
const newQuestions = ()=>{
    isModalOpen.value = true; operation_name.value = 'Crear'
    formQuestion.value[0].name = ''
    formQuestion.value[0].order = 0
}

watch(questionSelected,async (value)=>{

    router.get(`/surveys/details/${page.props.id}`, {
        categoryId:page.props.categoryId,
        questionId:value
        //page: page.value,
    }, {
        preserveState: true, // Evita que Vue destruya el estado del componente
        replace: true        // No satura el historial del botón "Atrás" del navegador
    });

    if(value) {
        const {data } = await getAnswersByQuestionApi(value)
        answersByQuestion.value = data
    }
})

//ANSWERS METHODS

// const getAnswersByQuestion = async (id) => {

//     try {
//         const {data,error} = await axios.get(`${apiHost}answer/show-by-question/${id}`)
        
//         if(data.answers)
//             return data.answers
//         return null
//     } catch (error) {
//         console.log(error)
//     }
// }
const createManyAnswers = async () => {
    const { success } = await createManyAnswersApi(formAnswer.value)
    if(success){
        answersByQuestion.value = await getAnswersByQuestionApi(questionSelected.value)
        formAnswer.value = [{
            name:'',
            order:0,
            question_id:questionSelected.value
            }]
        notify("Respuestas creadas correctamente");
        isModalOpen_answers.value = false;
    } else notify("Error al crear respuestas", true);
        }
            
const deleteAnswer = async (id,index) => {
    const success = await deleteAnswerApi(id)
    if(success){
            answersByQuestion.value.splice(index,1)
        notify("Respuesta eliminada");
    } else notify("Error al eliminar", true);
        }
        
const getAnswerToEdit = async (id) => {
    try {
        const {data, status} = await axios.get(`${apiHost}answer/show-one/${id}`)
        if(status==200){
            formAnswer.value[0].name = data.answer.name
            formAnswer.value[0].order = data.answer.order
            formAnswer.value[0].question_id = data.answer.question_id
            isModalOpen_answers.value = true
            operation_name.value = 'Editar'
            answerSelectedId.value = id
        }
        
    } catch (error) { console.log(error) }
    }
const updateAnswer = async (id) => {
    const { success } = await updateAnswerApi(id, formAnswer.value[0])
    if(success){
        notify("Respuesta actualizada");
        
        answersByQuestion.value = await getAnswersByQuestionApi(questionSelected.value)
        isModalOpen_answers.value = false;
    } else notify("Error al actualizar", true);
}
const incrementFormRow_answer = () =>{
    formAnswer.value.push({
        name:'',
        order:0,
        question_id:questionSelected.value
    })
}


// CATEGORIES METHODS
const updateCategories = async () => {
    const {data,errorFlag,responseMessage} = await getCategoriesBySurvey(page.props.id)
    
    if(data){
        
        categories.value = data
        
    }
    else if(errorFlag){
        notify(responseMessage, true);
    }
}

</script>

<style scoped>
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #64748b #0f172a;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #0f172a;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #64748b;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>

