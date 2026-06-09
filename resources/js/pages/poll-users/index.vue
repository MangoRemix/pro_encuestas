<template>
    <div>
        
    </div>
</template>
<script setup>
import { getAnswersByQuestion } from '@/composables/api/answers';
import { getQuestionsByCategory } from '@/composables/api/questions';
import { getCategoriesBySurvey, getSurvey } from '@/composables/api/surveys';
import { onMounted, ref, watch } from 'vue';

const survey = ref({
    data:null,
    categories:[]
})
const categories = ref([])

    onMounted(async ()=>{
        const {data,errorFlag} = await getSurvey(2)

        survey.value.data = data
        
    })

    watch(()=> survey.value.data,async (value)=>{
        
        if(value.id){
            const {data,errorFlag} = await getCategoriesBySurvey(value.id)
            
            if(data)
                categories.value = data
        }
        
    })

watch(categories, async (value) => {
    if (!value.length) return;
        
        try {
        // 1. Mapeamos cada categoría a una promesa que trae preguntas y sus respuestas
        const categoriesData = await Promise.all(value.map(async (category) => {
            const { data: questions } = await getQuestionsByCategory(category.id);
                
            if (!questions) return { category, questions: [] };

            // 2. Para cada categoría, lanzamos todas las peticiones de respuestas en paralelo
            const questionsWithAnswers = await Promise.all(questions.map(async (question) => {
                const { data: answers } = await getAnswersByQuestion(question.id);
                return { data: question, answers: answers || [] };
            }));

            return { category, questions: questionsWithAnswers };
        }));

        // 3. Asignamos al estado final de una sola vez
        survey.value.categories = categoriesData.map(item => ({
            data: item.category,
            questions: item.questions
        }));
        } catch (error) {
        console.error("Error cargando el cuestionario:", error);
        } finally {
        console.log("Carga completa:", survey.value);
        }
});
</script>
<style>
    
</style>