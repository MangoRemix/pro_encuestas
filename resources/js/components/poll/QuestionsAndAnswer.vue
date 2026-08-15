<template >
    <div class="border border-neutral-200 rounded-xl p-6 bg-white shadow-sm">

        <h3 class="mb-6 text-lg font-semibold text-gray-800">{{ question.order }}. {{ question.name }}</h3>
        
        <div class="flex flex-col gap-y-3 h-80 overflow-x-scroll">
            <div v-for="(answer,index) in answers"
                :key="answer.id"
                @click="selectedAnswer = answer.id"
                class="answer-option w-full min-h-10 cursor-pointer border border-gray-300 flex items-center px-4 hover:border-blue-500 transition-all duration-200 rounded-lg"
            >
            
                <input type="radio" v-model="selectedAnswer" :value="answer.id" :id="'answer-' + answer.id" name="answer" class="w-5 h-5 text-blue-600">
                <label :for="'answer-' + answer.id" class="ml-3 cursor-pointer text-gray-700 w-full">{{ answer.name }}</label>
            </div>
        </div>
        
        

    </div>
</template>
<script setup>
import { ref, watch } from 'vue';


const emits = defineEmits(['sendAnswer'])

    const props = defineProps(['question','answers'])
    
    const selectedAnswer = ref(null)

    // Reiniciar la selección cuando la pregunta cambie
    watch(() => props.question, () => {
        selectedAnswer.value = null
    }, { deep: true })

    watch(selectedAnswer,(value)=>{
        emits('sendAnswer',value)
    })
</script>
<style scoped>
    .answer-option:has(input:checked){
        background-color: #eff6ff;
        border-color: #3b82f6;
    }
    .answer-option:has(input:checked) label {
        font-weight: 600;
        color: #1e3a8a;
        
    }

    .answer-option:has(input:checked):hover label {
        color:#1e3a8a
    }
</style>

