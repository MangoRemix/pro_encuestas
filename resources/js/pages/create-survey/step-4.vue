<template >
    <MainLayout>
        <StepNavigation :items="steps" :current="current" />
        <div class="bg-white/30 backdrop-blur-md shadow-lg rounded-xl p-6 border border-blue-700/50 h-135 
        overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30">
            
            <h2 class="text-2xl font-bold text-white mb-6 underline">Resumen de la Encuesta</h2>

            <div v-if="fullSurvey" class="space-y-4">
                <div v-for="category in fullSurvey.categories" :key="category.id" class="border border-blue-800/50 rounded-xl overflow-hidden bg-blue-950/40">
                    <button @click="toggleCategory(category.id)" class="w-full p-4 text-white font-bold flex justify-between items-center hover:bg-white/60 cursor-pointer transition-colors">
                        {{ category.name }}
                        <Icon :icon="openItems.category === category.id ? 'ic:baseline-expand-less' : 'ic:baseline-expand-more'" class="text-xl" />
                    </button>

                    <div v-if="openItems.category === category.id" class="p-3 bg-blue-900/20 border-t border-blue-800/50">
                        <div v-for="question in category.questions" :key="question.id" class="mb-2 border border-blue-800/30 rounded-lg overflow-hidden bg-blue-950/60">
                            <button @click="toggleQuestion(question.id)" class="w-full p-3 text-sm text-blue-100 flex justify-between items-center hover:bg-white/40 cursor-pointer">
                                {{ question.name }}
                                <Icon :icon="openItems.question === question.id ? 'ic:baseline-expand-less' : 'ic:baseline-expand-more'" class="text-xs" />
                            </button>

                            <div v-if="openItems.question === question.id" class="p-4 bg-blue-950/80 text-blue-200 text-sm">
                                <div v-for="answer in question.answers" :key="answer.id" class="p-2 border-b border-blue-800/30 last:border-0 hover:bg-white/40">
                                    {{ answer.name }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mx-auto w-1/3 mb-3">
            <button @click="NextStep()" class="green-button-app cursor-pointer">
                Volver a listado de encuestas
            </button>    
        </div>
    </MainLayout>
</template>
<script setup>
import StepNavigation from '@/components/StepNavigation.vue';
import { showFullSurvey } from '@/composables/api/surveys';
import MainLayout from '@/layouts/main-layout.vue';
import { currentStep, stepsBreadcrumb } from '@/store/store';
import { router, usePage } from '@inertiajs/vue3';
import { onMounted, reactive, ref } from 'vue';
import { Icon } from "@iconify/vue";

const page = usePage()

const steps = stepsBreadcrumb
const current = currentStep

const fullSurvey = ref(null)

const openItems = reactive({
    category: null,
    question: null
})

const toggleCategory = (id) => {
    openItems.category = openItems.category === id ? null : id
    openItems.question = null
}

const toggleQuestion = (id) => {
    openItems.question = openItems.question === id ? null : id
}

onMounted(async ()=>{
    current.value = 'Resumen'
    const {data} = await showFullSurvey(page.props.surveyId)
    fullSurvey.value = data
    console.log(fullSurvey.value)
})

const NextStep = () =>{
    router.get('/surveys',{
        surveyId:page.props.surveyId
    })
}
</script>
<style scoped>
    
</style>