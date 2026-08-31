<template>
  <div class="max-w-2xl mx-auto my-8 px-6 py-8 bg-white border border-gray-200 rounded-2xl shadow-sm">
    <h2 class="text-xl font-bold text-gray-900 mb-8">
      Crear Nueva Encuesta
    </h2>

    <form @submit.prevent="handleSubmit" class="space-y-6">
      <div class="flex flex-col gap-2">
        <label for="name" class="text-sm font-medium text-gray-700">
          Nombre de la Encuesta
        </label>
        <input 
          type="text" 
          id="name" 
          v-model="form.name" 
          placeholder="Ej. Satisfacción al Cliente"
          class="inputs-form w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition-all outline-none"
          required
        />
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="flex flex-col gap-2">
          <label for="init_date" class="text-sm font-medium text-gray-700">
            Fecha de Inicio
          </label>
          <input
          type="date" 
          id="init_date" 
          v-model="form.init_date" 
            class="inputs-form w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition-all outline-none"
          required
        />
      </div>

        <div class="flex flex-col gap-2">
          <label for="finish_date" class="text-sm font-medium text-gray-700">
            Fecha de Finalización
        </label>
        <input 
          type="date" 
          id="finish_date" 
          v-model="form.finish_date" 
          :min="form.init_date"
            class="inputs-form w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition-all outline-none"
          required
        />
      </div>
  </div>

      <button
        type="submit"
        :disabled="loading"
        class="w-full px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
      >
        {{ loading ? 'Guardando...' : 'Crear Encuesta' }}
      </button>
    </form>

    <div class="mt-6">
      <NotificationBox v-if="message || isError" :message="message" :isError="isError" />
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import axios from 'axios';
import NotificationBox from '../notification-box.vue';
import { apiHost } from '@/store/store.js';
import { formatedDate } from '@/composables/shared.js';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const {surveyId} = defineProps(['surveyId'])

// Estado del formulario
const form = reactive({
  name: '',
  init_date: '',
  finish_date: ''
});

// Estados de la petición
const loading = ref(false);
const message = ref('');
const isError = ref(false);

  onMounted(async ()=>{
    if(surveyId > 0){
      const survey = await getSurvey(surveyId)
      form.name = survey.name
      form.init_date = formatedDate(survey.init_date); 
      form.finish_date = formatedDate(survey.finish_date); 
    }
    

  })


  const getSurvey = async (id) => {
    try {
        const response = await axios.get(`${apiHost}survey/show-one/${id}`)
        
        if(response.data.length > 0)
            return response.data[0]
        else
            return 'No hay encuestas registradas.'
    } catch (error) {
        console.log(error)   
    }
  }

// Manejador del envío
const handleSubmit = async () => {
  loading.value = true;
  message.value = '';
  isError.value = false;

  try {
    // Ajusta la URL según la configuración de tu entorno
    let response = null
    if(!surveyId)
      response = await axios.post(`${apiHost}survey/create`, form);
    else
      response = await axios.put(`${apiHost}survey/update/${surveyId}`, form);
    
    message.value = '¡Encuesta creada con éxito!';
    if(response.status == 200){
      getSurvey(surveyId)
      
    }else{
      if(response.status == 201){
        //console.log(response)
        setTimeout(() => {
          if(response.data.data.id)
          router.get('/surveys/create-survey/step-2',{
            surveyId:response.data.data.id
          })
        }, 250);
        //setTimeout(() => {
        //  if(response.data.data.id)
        //  router.get('/categories/create',{
        //    surveyId:response.data.data.id
        //  })
        //}, 750);
      }
    }
    // Limpiar el formulario
    form.name = '';
    form.init_date = '';
    form.finish_date = '';
  } catch (error) {
    isError.value = true;
    if (error.response?.data?.message) {
      message.value = `Error: ${error.response.data.message}`;
    } else {
      message.value = 'Ocurrió un error al procesar la solicitud.';
    }
  } finally {
  loading.value = false;
  }
};
</script>

