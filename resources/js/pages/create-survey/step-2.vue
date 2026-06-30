<template>
    <Head title="Paso2: crear-categorías" />
    <MainLayout>
        <NotificationBox class="w-120 absolute right-0 top-10" 
        v-if="message" :is-error="isError" :message="message" ></NotificationBox>
        <div class="w-200 flex items-center mx-auto">
            <StepNavigation :items="steps" :current="current" />
        </div>
        <div class="max-w-2xl mx-auto py-10 px-4">
            <CategoryForm :survey_id="page.props.surveyId" @update-categories="updateCategories" />
        </div>

        <div class="w-full flex items-center justify-between h-15">
            <span class="text-white">Categorias existentes ({{ categories.length }})</span>

            <div class="w-fit">
                <button @click="NextStep()"  class="yellow-button-app cursor-pointer" :disabled="!categories.length>0">
                    
                    Cargar preguntas
                    
                </button>
            </div>
            
        </div>
        

        <div class="bg-white/30 backdrop-blur-md shadow-lg rounded-xl p-6 border border-blue-700/50 
            w-full
            h-125">
            <h3 class="text-xl text-center text-white font-extrabold mb-3">Listado de Categorías</h3>
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
                        <tr  v-for="category in categories" class="text-white border-b border-neutral-400">
                            <td class="py-2 w-30">
                                {{ category?.order }}
                            </td>
                            <td class="py-2">
                                {{ category?.name }}
                            </td>
                            <td class="py-2 w-45">
                                <div class="flex items-center justify-center gap-x-3 w-full">
                                    <Link :href="`/categories/details/${category.id}`">
                                        <Icon class="text-lg md:text-2xl text-blue-600 hover:text-blue-500 cursor-pointer" icon="ic:baseline-remove-red-eye"/>
                                    </Link>
                                    
                                    <Icon class="text-2xl text-yellow-600 hover:text-yellow-500 cursor-pointer" icon="ic:baseline-edit"/>
                                    <Icon class="text-lg md:text-2xl text-red-600 hover:text-red-500 cursor-pointer" icon="ic:baseline-restore-from-trash"/>
                                </div>
                            </td>
                        </tr>
                        
                    </tbody>
                </table>
            </div>
        </div>
    </MainLayout>
</template>
<script setup>
const message = ref('')
const isError = ref(false)
import { onMounted, ref } from 'vue';
import MainLayout from '@/layouts/main-layout.vue';
import CategoryForm from '@/components/forms/category-form.vue';
import { Icon } from '@iconify/vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { getCategoriesBySurvey } from '@/composables/api/surveys';
import NotificationBox from '@/components/notification-box.vue';
import { currentStep, stepsBreadcrumb } from '@/store/store';
import StepNavigation from '@/components/StepNavigation.vue';

const page = usePage()
const categories = ref([])
const steps = stepsBreadcrumb
const current = currentStep;

onMounted(async() => {
    const {data,errorFlag} = await getCategoriesBySurvey(parseInt(page.props.surveyId))

    categories.value = data
    current.value = 'Categorías'
});

const updateCategories = async (status)=>{
    try {
       message.value = status.message;
       isError.value = !status.success;

       setTimeout(() => { message.value = '' }, 3000);

       if (status.success) {
       const {data} = await getCategoriesBySurvey(page.props.surveyId)
       categories.value = data
    }
    } catch (error) {
        console.error(error)
    }
}
const NextStep = ()=>{
    router.get('/surveys/create-survey/step-3',{
        surveyId:page.props.surveyId
    })
}
</script>

