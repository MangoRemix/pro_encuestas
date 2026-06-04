<template>
    <Head :title="'Categorías'" />
    <MainLayout>
        <NotificationBox v-if="message || isError? true:false" :message="message" :is-error="isError" class="absolute z-10 right-0 top-0 w-100"/>
        <div class="w-100 min-h-10 mx-auto my-3 flex flex-col ">
            
            <select name="" v-model="surveySelected" id="" class="inputs-form bg-white ">
                <option :value="0">Seleccione encuesta</option>
                <option :value="survey.id" class="p-2 text-neutral-800" v-for="survey in surveys"> {{ survey.name }}</option>
                
            </select>
        </div>
        <div class="flex space-x-2">
            <div class="bg-white/30 backdrop-blur-md shadow-lg rounded-xl p-6 border border-blue-700/50 
            w-full sm:w-[75%] md:w-[55%] lg:w-[35%]
            h-125 overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30">
                <ul class="text-blue-100 mt-2">
                    <li @click="categorySelected = category.id" v-for="category in categories" class="cursor-pointer hover:underline hover:text-yellow-400 hover:font-bold transition-all duration-75 py-1">
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
                <div id="table-body" class="w-full max-h-100 overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30">
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
                                        
                                        <Icon @click="" class="text-lg md:text-2xl text-yellow-600 hover:text-yellow-500 cursor-pointer" icon="ic:baseline-edit"/>
                                        <Icon class="text-lg md:text-2xl text-red-600 hover:text-red-500 cursor-pointer" icon="ic:baseline-restore-from-trash"/>
                                    </div>
                                </td>
                            </tr>
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
<script setup>
import NotificationBox from '@/components/notification-box.vue';
import { getQuestionsByCategory } from '@/composables/api/questions';
import { getCategoriesBySurvey, getSurveys } from '@/composables/api/surveys';
import MainLayout from '@/layouts/main-layout.vue';
import { Icon } from '@iconify/vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';

const questions = ref([])
const categories = ref([])
const surveys = ref([])
const page = usePage()
const categorySelected = ref(0)
const surveySelected = ref(0)

const message = ref()
const isError = ref(false)

onMounted(async()=>{

    const {data,errorFlag} = await getSurveys()
    
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

    if(data)
        surveys.value = data
})

watch(surveySelected,async (value)=>{
    
    router.get('/categories', {
        surveyId:value,
        //page: page.value,
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

    router.get('/categories', {
        surveyId:surveySelected.value,
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

</script>
<style scoped>
    /* #table-body tbody tr:nth-child(odd) {
        background-color: rgba(255, 255, 255, 0.03);
    }

    
    #table-body tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.15);
        transition: background-color 0.2s ease-in-out;
    }

    
    #table-body tbody tr {
        cursor: pointer;
    }

    #table-body tbody tr:hover td:nth-child(2) {
        color:oklch(85.2% 0.199 91.936);
    } */

</style>