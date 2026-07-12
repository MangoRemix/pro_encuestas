<template>
    <Head :title="'Categorías'" />
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
            <button @click="newQuestions()" class="text-white font-bold flex items-center gap-x-3 bg-yellow-400 cursor-pointer hover:bg-yellow-300 rounded-2xl px-2">
                Crear preguntas
                <Icon class="h-9 w-9 p-1 " icon="ic:outline-plus" />
            </button>

        </div>
        <div class="flex space-x-2">
            <div class="bg-white/30 backdrop-blur-md shadow-lg rounded-xl p-6 border border-blue-700/50 
            w-full sm:w-[75%] md:w-[55%] lg:w-[35%]
            h-125 overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30">
                <h3 class="text-xl text-center text-white font-extrabold mb-3">Listado de Categorías</h3>
                <ul class="text-blue-100 mt-2">
                    <li @click="categorySelected = category.id" v-for="category in categories" 
                    :class="`cursor-pointer hover:underline hover:text-yellow-400 hover:font-bold transition-all duration-75 py-1
                    ${categorySelected==category.id?'text-yellow-400':''}
                    `">
                        {{ category.name }}
                    </li>
                </ul>
            </div>
                
            <div class="bg-white/30 backdrop-blur-md shadow-lg rounded-xl p-6 border border-blue-700/50 
            w-full
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
                                <td class="py-2">
                                    <Link :href="`/questions/details/${question.id}`">
                                        {{ question.name }}
                                    </Link>
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
        </div>

        <Modal :show="isModalOpen" @close="isModalOpen = false">
            <!-- FORMULARIO CATEGORIES -->
            
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
    </MainLayout>
</template>
<script setup>
import NotificationBox from '@/components/notification-box.vue';
import Modal from '@/components/modal.vue';
import { createMany, getQuestion, getQuestionsByCategory } from '@/composables/api/questions';
import { getCategoriesBySurvey, getSurvey, getSurveys } from '@/composables/api/surveys';
import MainLayout from '@/layouts/main-layout.vue';
import { Icon } from '@iconify/vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';

const operation_name = ref('create')
const isModalOpen = ref(false)

const questions = ref([])
const categories = ref([])
const survey = ref([])
const page = usePage()
const categorySelected = ref(0)
const surveySelected = ref(0)
const questionSelected = ref(0)

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

    const {data,errorFlag} = await getSurvey(page.props.id)
    
    setTimeout(() => {
        if(page.props.categoryId){
            surveySelected.value = page.props.id
            categorySelected.value = parseInt(page.props.categoryId)
        }
        else{
            if(page.props.id)
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

const incrementFormRow = () =>{
    formQuestion.value.push({
        name:'',
        order:0,
        category_id:parseInt(page.props.categoryId)
    })
}

const getQuestionToEdit = async (id) => {
    try {
        const {data,errorFlag,responseMessage} = await getQuestion(id)
        console.log("question: ",data)
        if(data){
            
            formQuestion.value[0].name = data.name
            formQuestion.value[0].order = data.order
            isModalOpen.value = true
            operation_name.value = 'Editar'
            questionSelected.value = id
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
        
        const {data,errorFlag,status} = await createMany(formQuestion.value)
        
        if(data){
            categories.value = await getQuestionsByCategory(page.props.categoryId)
            message.value = data
            formQuestion.value = [
                {
                    name:'',
                    order:0,
                    category_id:parseInt(page.props.categoryId)
                }
            ]

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

const newQuestions = ()=>{
    isModalOpen.value = true; operation_name.value = 'Crear'
    formQuestion.value[0].name = ''
    formQuestion.value[0].order = 0
}

</script>