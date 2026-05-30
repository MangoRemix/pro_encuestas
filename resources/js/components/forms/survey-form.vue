
<template>
  <div class="max-w-md mx-auto my-8 p-6 bg-white border border-gray-200 rounded-xl shadow-sm w-200">
    <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">
      Crear Nueva Encuesta
    </h2>

    <form @submit.prevent="handleSubmit" class="space-y-5">
      <!-- Campo: Nombre -->
      <div class="flex flex-col gap-1.5">
        <label for="name" class="text-sm font-semibold text-gray-700">
          Nombre de la Encuesta:
        </label>
        <input 
          type="text" 
          id="name" 
          v-model="form.name" 
          placeholder="Ej. Satisfacción al Cliente"
          class="inputs-form"
          required
        />
      </div>

      <!-- Campo: Fecha de Inicio -->
      <div class="flex flex-col gap-1.5">
        <label for="init_date" class="text-sm font-semibold text-gray-700">
          Fecha de Inicio:
        </label>
        <input 
          type="date" 
          id="init_date" 
          v-model="form.init_date" 
          class="inputs-form"
          required
        />
      </div>

      <!-- Campo: Fecha de Fin -->
      <div class="flex flex-col gap-1.5">
        <label for="finish_date" class="text-sm font-semibold text-gray-700">
          Fecha de Finalización:
        </label>
        <input 
          type="date" 
          id="finish_date" 
          v-model="form.finish_date" 
          :min="form.init_date"
          class="inputs-form"
          required
        />
      </div>

      <!-- Botón de Envío -->
      <button 
        type="submit" 
        :disabled="loading"
        :class="`primary-button-app hover:${form.name?'cursor-pointer':''}`"
      >
        {{ loading ? 'Guardando...' : 'Crear Encuesta' }}
      </button>
    </form>

    <!-- Mensajes de Estado -->
    <NotificationBox v-if="message || isError? true:false" :message="message" :isError="isError" />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import axios from 'axios';
import notificationBox from '../notification-box.vue';
import { apiHost } from '@/store/store.js';
import { formatedDate } from '@/composables/shared.js';

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
    if(response.status == 200)
      getSurvey(surveyId)
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
