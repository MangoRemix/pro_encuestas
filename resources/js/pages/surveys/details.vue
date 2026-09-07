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
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            <!-- CATEGORIAS -->
            <div class="lg:col-span-3 bg-slate-600/50 backdrop-blur-md shadow-lg rounded-xl p-4 border border-blue-700/50 h-150 flex flex-col">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg text-white font-extrabold">Categorías</h3>
                    <button @click="isModalOpen_categories = true" class="btn-circle btn-circle-yellow w-8 h-8 cursor-pointer">
                        <Icon class="text-xl text-white" icon="ic:outline-plus" />
                    </button>
                </div>
                <div class="overflow-y-auto custom-scrollbar flex-1">
                    <ul class="text-blue-100 space-y-1">
                        <li @click="categorySelected = category.id" v-for="category in categories" :key="category.id"
                            :class="`cursor-pointer px-3 py-2 rounded-lg transition-all duration-200 
                            ${categorySelected==category.id ? 'bg-blue-600/50 text-white font-bold' : 'hover:bg-slate-700/50'}`">
                            {{ category.name }}
                        </li>
                    </ul>
                </div>
            </div>

            <!-- PREGUNTAS -->
            <div class="lg:col-span-5 bg-slate-600/50 backdrop-blur-md shadow-lg rounded-xl p-4 border border-blue-700/50 h-150 flex flex-col">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg text-white font-extrabold">Preguntas</h3>
                    <button @click="newQuestions()" class="btn-circle btn-circle-yellow w-8 h-8 cursor-pointer disabled:opacity-50"
                        :disabled="!categorySelected">
                        <Icon class="text-xl text-white" icon="ic:outline-plus" />
                    </button>
                </div>
                
                <div class="bg-slate-900/50 border border-slate-700 rounded-lg overflow-hidden flex-1 flex flex-col min-h-0">
                    <div class="overflow-y-auto custom-scrollbar flex-1 w-full">
                        <table class="w-full text-left border-collapse table-fixed">
                            <thead class="sticky top-0 bg-slate-900 z-10">
                                <tr class="text-white text-xs uppercase tracking-wider">
                                    <th class="p-3 w-16">Ord</th>
                                    <th class="p-3">Nombre</th>
                                    <th class="p-3 w-24 text-center">Acc</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/50">
                                <tr v-if="questions.length === 0" class="text-slate-400 italic text-sm">
                                    <td colspan="3" class="p-4 text-center">Selecciona una categoría</td>
                                </tr>
                                <tr v-for="(question,index) in questions" :key="question.id" 
                                    @click="questionSelected=question.id"
                                    :class="`cursor-pointer transition-colors ${questionSelected==question.id ? 'bg-blue-600/30' : 'hover:bg-slate-600/30'}`">
                                    <td class="p-3 font-medium text-slate-200">{{ question.order }}</td>
                                    <td class="p-3 text-slate-200 whitespace-normal wrap-break-words" :title="question.name">{{ question.name }}</td>
                                    <td class="p-3 w-24">
                                        <div class="flex items-center justify-center gap-x-2">
                                            <Link :href="`/questions/details/${question.id}`" class="text-blue-400 hover:text-blue-300">
                                                <Icon class="text-lg" icon="ic:baseline-remove-red-eye"/>
                                            </Link>
                                            
                                            <Icon @click="getQuestionToEdit(question.id)" class="text-lg text-yellow-500 hover:text-yellow-400 cursor-pointer" icon="ic:baseline-edit"/>
                                            <Icon @click.stop="deleteQuestion(question.id)" class="text-lg text-red-500 hover:text-red-400 cursor-pointer" icon="ic:baseline-restore-from-trash"/>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- RESPUESTAS -->
            <div class="lg:col-span-4 bg-slate-600/50 backdrop-blur-md shadow-lg rounded-xl p-4 border border-blue-700/50 h-150 flex flex-col">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg text-white font-extrabold">Respuestas</h3>
                    <button @click="newAnswers()" class="btn-circle btn-circle-yellow w-8 h-8 cursor-pointer disabled:opacity-50"
                        :disabled="!questionSelected">
                        <Icon class="text-xl text-white" icon="ic:outline-plus" />
                    </button>
                </div>
                
                <div class="bg-slate-900/50 border border-slate-700 rounded-lg overflow-hidden flex-1 flex flex-col min-h-0">
                    <div class="overflow-y-auto custom-scrollbar flex-1 w-full">
                        <table class="w-full text-left border-collapse table-fixed">
                            <thead class="sticky top-0 bg-slate-900 z-10">
                                <tr class="text-white text-xs uppercase tracking-wider">
                                    <th class="p-3 w-16">Ord</th>
                                    <th class="p-3">Nombre</th>
                                    <th class="p-3 w-24 text-center">Acc</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/50 text-slate-200">
                                <tr v-if="!questionSelected" class="text-slate-400 italic text-sm">
                                    <td colspan="3" class="p-4 text-center">Selecciona una pregunta para ver sus respuestas</td>
                                </tr>
                                <tr v-else-if="answersByQuestion.length === 0" class="text-slate-400 italic text-sm">
                                    <td colspan="3" class="p-4 text-center">Sin respuestas</td>
                                </tr>
                                <tr v-for="(answer,index) in answersByQuestion" :key="answer.id" class="hover:bg-slate-600/30 transition-colors">
                                    <td class="p-3 font-medium">{{ answer.order }}</td>
                                    <td class="p-3 text-slate-200 whitespace-normal wrap-break-words" :title="answer.name">{{ answer.name }}</td>
                                    <td class="p-3 w-24">
                                        <div class="flex items-center justify-center gap-x-2">
                                            <Icon @click="getAnswerToEdit(answer.id)" class="text-lg text-yellow-500 hover:text-yellow-400 cursor-pointer" icon="ic:baseline-edit"/>
                                            <Icon @click="deleteAnswer(answer.id,index)" class="text-lg text-red-500 hover:text-red-400 cursor-pointer" icon="ic:baseline-restore-from-trash"/>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODALES -->
        <Modal :show="isModalOpen" @close="isModalOpen = false">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-center text-slate-800 mb-6">
                    {{ operation_name }} pregunta
                </h2>
                <form @submit.prevent="operation_name =='Crear'?createManyQuestions():updateQuestion(questionSelected)" class="w-full max-w-lg mx-auto">
                    <div class="flex items-center justify-end space-x-3 mb-4">
                        <button type="button" @click.prevent="incrementFormRow" class="btn-circle btn-circle-yellow w-10 h-10" v-if="operation_name!='Editar'">
                            <Icon class="text-2xl text-white" icon="ic:outline-plus" />
                        </button>
                        <button type="submit" class="btn-circle btn-circle-blue w-10 h-10">
                            <Icon class="text-2xl text-white" icon="ic:round-save" />
                        </button>
                    </div>
                    <div class="max-h-[60vh] overflow-y-auto custom-scrollbar pr-2">
                        <div v-for="(formRow,index) in formQuestion" :key="index" class="mb-4 p-4 border border-slate-200 rounded-lg bg-slate-50 relative">
                            <button type="button" v-if="index > 0" @click="formQuestion.splice(index, 1)" class="absolute top-2 right-2 text-red-500 hover:text-red-700">
                                <Icon icon="ic:baseline-close" class="text-xl" />
                            </button>
                            <div class="font-bold mb-2 text-sm text-slate-700">Pregunta {{ index+1 }}</div>
                            <div class="grid grid-cols-4 gap-2">
                                <div class="col-span-1">
                                    <label class="text-xs font-bold text-slate-600 block mb-1">Orden</label>
                                    <input required v-model="formRow.order" min="1" type="number" class="inputs-form">
                                </div>
                                <div class="col-span-3">
                                    <label class="text-xs font-bold text-slate-600 block mb-1">Nombre</label>
                                    <input required minlength="5" v-model="formRow.name" type="text" class="inputs-form">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </Modal>
        
        <Modal :show="isModalOpen_answers" @close="isModalOpen_answers = false">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-center text-slate-800 mb-6">
                    {{ operation_name }} respuesta
                </h2>
                <form @submit.prevent="operation_name =='Crear'?createManyAnswers():updateAnswer(answerSelectedId)" class="w-full max-w-lg mx-auto">
                    <div class="flex items-center justify-end space-x-3 mb-4">
                        <button type="button" @click.prevent="incrementFormRow_answer" class="btn-circle btn-circle-yellow w-10 h-10" v-if="operation_name!='Editar'">
                            <Icon class="text-2xl text-white" icon="ic:outline-plus" />
                        </button>
                        <button type="submit" class="btn-circle btn-circle-blue w-10 h-10">
                            <Icon class="text-2xl text-white" icon="ic:round-save" />
                        </button>
                    </div>
                    <div class="max-h-[60vh] overflow-y-auto custom-scrollbar pr-2">
                        <div v-for="(formRow,index) in formAnswer" :key="index" class="mb-4 p-4 border border-slate-200 rounded-lg bg-slate-50 relative">
                            <button type="button" v-if="index > 0" @click="formAnswer.splice(index, 1)" class="absolute top-2 right-2 text-red-500 hover:text-red-700">
                                <Icon icon="ic:baseline-close" class="text-xl" />
                            </button>
                            <div class="font-bold mb-2 text-sm text-slate-700">Respuesta {{ index+1 }}</div>
                            <div class="grid grid-cols-4 gap-2">
                                <div class="col-span-1">
                                    <label class="text-xs font-bold text-slate-600 block mb-1">Orden</label>
                                    <input required v-model="formRow.order" min="1" type="number" class="inputs-form">
                                </div>
                                <div class="col-span-3">
                                    <label class="text-xs font-bold text-slate-600 block mb-1">Nombre</label>
                                    <input required minlength="5" v-model="formRow.name" type="text" class="inputs-form">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
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

import { Icon } from '@iconify/vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref, watch } from 'vue';
import CategoryForm from '@/components/forms/category-form.vue';
import Modal from '@/components/modal.vue';
import NotificationBox from '@/components/notification-box.vue';

import { useAnswers } from '@/composables/api/answers';
import { createMany, getQuestion, getQuestionsByCategory } from '@/composables/api/questions';
import { getCategoriesBySurvey, getSurvey } from '@/composables/api/surveys';
import { useNotification } from '@/composables/useNotification';
import MainLayout from '@/layouts/main-layout.vue';
import { apiHost } from '@/store/store';


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

// Function to delete a question
const deleteQuestion = async (id) => {
    // Placeholder: You'll need to implement the actual API call here.
    // Example: You might have a deleteQuestionApi function in your composables.
    console.log(`Deleting question with id: ${id}`);
    // const success = await deleteQuestionApi(id); // Uncomment and adapt this line
    // if (success) {
    //     // Refresh questions list after deletion
    //     const { data: questions_ } = await getQuestionsByCategory(categorySelected.value);
    //     questions.value = questions_;
    //     notify("Pregunta eliminada correctamente");
    // } else {
    //     notify("Error al eliminar la pregunta", true);
    // }
};

onMounted(async()=>{

    const {data} = await getSurvey(page.props.id)
    
    setTimeout(() => {
        if(page.props.categoryId){
            surveySelected.value = page.props.id
            categorySelected.value = parseInt(page.props.categoryId)
        } else if(page.props.id){
                surveySelected.value = page.props.id
        }
    }, 750);   

    if(data) {
survey.value = data
}
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
    } else if(errorFlag){
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
    } else if(errorFlag) {
    notify(responseMessage, true);
}
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

    } else if(errorFlag) {
    notify(responseMessage, true);
}
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
    } else {
    notify("Error al crear respuestas", true);
}
        }
            
const deleteAnswer = async (id,index) => {
    const success = await deleteAnswerApi(id)

    if(success){
            answersByQuestion.value.splice(index,1)
        notify("Respuesta eliminada");
    } else {
    notify("Error al eliminar", true);
}
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
        
    } catch (error) {
 console.log(error) 
}
    }
const updateAnswer = async (id) => {
    const { success } = await updateAnswerApi(id, formAnswer.value[0])

    if(success){
        notify("Respuesta actualizada");
        
        answersByQuestion.value = await getAnswersByQuestionApi(questionSelected.value)
        isModalOpen_answers.value = false;
    } else {
    notify("Error al actualizar", true);
}
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
        
    } else if(errorFlag){
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
