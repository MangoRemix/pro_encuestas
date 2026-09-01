<template>
    <Head title="Paso2: crear-categorías" />
    <MainLayout>
        <NotificationBox class="w-120 absolute right-0 top-10" 
        v-if="message" :is-error="isError" :message="message" ></NotificationBox>
        
        <StepNavigation :items="steps" :current="current" />
        
        <div class="max-w-2xl mx-auto py-10 px-4">
            <CategoryForm :survey_id="page.props.surveyId" @update-categories="updateCategories" />
        </div>

        <div class="w-full flex items-center justify-end h-15">
            
            <div class="w-fit">
                <button @click="NextStep()"  class="yellow-button-app cursor-pointer flex items-center gap-x-2" :disabled="!categories.length>0">
                    <Icon class="text-2xl " icon="ic:outline-plus" />
                    Cargar preguntas
                    
                </button>
            </div>
            
        </div>
        

        <div class="bg-gray-500/50 border border-slate-700 rounded-lg overflow-hidden w-full">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-700 bg-slate-900/50 text-white text-xs uppercase tracking-wider">
                            <th class="p-4 w-30">Orden</th>
                            <th class="p-4">Nombre</th>
                            <th class="p-4 text-center w-55">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50 custom-scrollbar">
                        <tr v-for="category in categories" :key="category.id" class="text-slate-200 hover:bg-slate-600/30 transition-colors">
                            <td class="p-4">{{ category?.order }}</td>
                            <td class="p-4">{{ category?.name }}</td>
                            <td class="p-4">
                                <div class="flex items-center justify-center gap-x-3 w-full">
                                    <Link :href="`/categories/details/${category.id}`">
                                        <Icon class="text-xl text-blue-400 hover:text-blue-300 cursor-pointer" icon="ic:baseline-remove-red-eye"/>
                                    </Link>
                                    
                                    <Icon class="text-xl text-yellow-500 hover:text-yellow-400 cursor-pointer" icon="ic:baseline-edit"/>
                                    <Icon class="text-xl text-red-500 hover:text-red-400 cursor-pointer" icon="ic:baseline-restore-from-trash"/>
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
    console.log(page.props)
    router.get('/surveys/create-survey/step-3',{
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

