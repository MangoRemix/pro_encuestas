
<template>
  <div class="max-w-md mx-auto my-8 p-6 bg-white border border-gray-200 rounded-xl shadow-sm">
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
import { ref, reactive } from 'vue';
import axios from 'axios';
import NotificationBox from './notification-box.vue';

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

// Manejador del envío
const handleSubmit = async () => {
  loading.value = true;
  message.value = '';
  isError.value = false;

  try {
    // Ajusta la URL según la configuración de tu entorno
    const response = await axios.post('http://localhost:8000/api/survey/create', form);
    
    message.value = '¡Encuesta creada con éxito!';
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
