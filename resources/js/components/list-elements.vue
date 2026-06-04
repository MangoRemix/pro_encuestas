<script setup>
import { selectedListComponentValue } from '@/store/store';
import { onMounted } from 'vue';

    const {elements,defaultValue} = defineProps(['elements','defaultValue'])
    const selectedListValue = selectedListComponentValue
    onMounted(()=>{
        
        selectedListValue.value = defaultValue
        
    })
</script>
<template>

    <select v-model="selectedListValue" name="" id="" class="inputs-form bg-white">
        <option :value="0">

        </option>
        <option :value="element.id" v-for="element in elements">
            {{ element.name }}
        </option>
    </select>

</template>