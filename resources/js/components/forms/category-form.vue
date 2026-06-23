<script setup>
import { ref } from 'vue';
import axios from 'axios';
const emits = defineEmits(['update-categories'])
const {survey_id} = defineProps(['survey_id'])

const form = ref({
    name: '',
    order:1,
    survey_id:parseInt(survey_id)
});

const submit = async () => {
    try {
        const response = await axios.post('/api/category/create', form.value);
        alert('Categoría creada con éxito');
        if(response.status == 201){
            emits('update-categories',true)
            form.value.name = '';
        }
            
    } catch (error) {
        console.error('Error al crear categoría', error);
        alert('Error al guardar la categoría');
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="p-6 bg-white rounded-lg shadow-sm border border-gray-200">
        <h2 class="text-xl font-semibold mb-6 text-gray-800">Nueva Categoría</h2>
        
        <div class="mb-4 flex items-center space-x-3">
            <div class="w-18">
                <label class="block text-sm font-medium text-gray-700" for="">Orden</label>
                <input v-model="form.order" type="number" class="inputs-form">
            </div>
            <div class="w-full">

                <label class="block text-sm font-medium text-gray-700">Nombre de la Categoría</label>
                <input 
                    v-model="form.name" 
                    type="text" 
                    class="inputs-form"
                    required
                />
            </div>
        </div>
        
        <div class="flex justify-end mt-6">
            <button 
                type="submit" 
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200"
            >
                Guardar Categoría
            </button>
        </div>
    </form>
</template>
