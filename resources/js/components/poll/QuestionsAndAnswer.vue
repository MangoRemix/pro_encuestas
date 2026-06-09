<template >
    <div>

        <h3 class="mb-5">{{ question.order }}. {{ question.name }}</h3>
        
        <div class="flex flex-col gap-y-5">
            <div class="" v-for="answer in answers">
                <input  :value="answer.id" type="radio" name="answer" id=""> <label for="answer">{{ answer.name }}</label>
            </div>
        </div>
        
        

    </div>
</template>
<script setup>
    const {question,answers} = defineProps(['question','answers'])
</script>
<style lang="">
    
</style>













