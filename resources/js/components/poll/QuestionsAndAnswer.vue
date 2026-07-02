<template >
    <div class="border border-neutral-800 rounded-xl p-2 ">

        <h3 class="mb-5">{{ question.order }}. {{ question.name }}</h3>
        
        <div class="flex flex-wrap gap-y-5 gap-x-2 justify-around">
            <div v-for="(answer,index) in answers"
                @click="selectedAnswer = answer.id"
                class="answer-option w-100 h-20 cursor-pointer border border-neutral-500 flex items-center gap-x-2 px-2 hover:bg-blue-500 hover:text-white transition-all duration-100 rounded-lg" 
            >
            
                <input type="radio" v-model="selectedAnswer" :value="answer.id" :id="'answer-' + answer.id" name="answer">
                <label :for="'answer-' + answer.id">{{ answer.name }}</label>
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
        background-color: oklch(62.3% 0.214 259.815);
    }
    .answer-option:has(input:checked) label {
        font-weight: bold;
        color: white;
        
    }

    .answer-option:has(input:checked):hover label {
        color:white
    }
</style>

