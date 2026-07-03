<template>
  <Head title="Gestión de usuarios: Crear"/>
  <MainLayout>
    <div class="w-full flex justify-center">
      <h3 class=" mx-auto text-white font-bold text-3xl underline">Gestión de usuarios</h3>
    </div>
    
    <div class="max-w-md mx-auto my-8 p-6 bg-white border border-gray-200 rounded-xl shadow-sm">
      <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">
        Registrar Usuario
      </h2>

      <form @submit.prevent="handleSubmit" class="space-y-5">
        <div class="flex flex-col gap-1.5">
          <label for="name" class="text-sm font-semibold text-gray-700">Nombre Completo:</label>
          <input type="text" id="name" v-model="form.name" class="inputs-form" required />
        </div>

        <div class="flex flex-col gap-1.5">
          <label for="email" class="text-sm font-semibold text-gray-700">Correo Electrónico:</label>
          <input type="email" id="email" v-model="form.email" class="inputs-form" required />
        </div>

        <div class="flex flex-col gap-1.5">
          <label for="password" class="text-sm font-semibold text-gray-700">Contraseña:</label>
          <input type="password" id="password" v-model="form.password" class="inputs-form" required />
        </div>

        <div class="flex flex-col gap-1.5">
          <label for="sex_id" class="text-sm font-semibold text-gray-700">Sexo:</label>
          <select id="sex_id" v-model="form.sex_id" class="inputs-form" required>
            <option value="" disabled>Seleccione...</option>
            <option v-for="sex in sexes" :key="sex.id" :value="sex.id">
              {{ sex.abbreviation }}
            </option>
          </select>
        </div>

        <div class="flex flex-col gap-1.5">
          <label for="rol_id" class="text-sm font-semibold text-gray-700">Rol de Usuario:</label>
          <select id="rol_id" v-model="form.rol_id" class="inputs-form" required>
            <option value="" disabled>Seleccione...</option>
            <option :value="1">Encuestador</option>
            <option :value="3">Administrador</option>
          </select>
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="primary-button-app w-full cursor-pointer"
        >
          {{ loading ? 'Guardando...' : 'Crear Usuario' }}
        </button>
      </form>

      <NotificationBox v-if="message" :message="message" :isError="isError" />
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import NotificationBox from '@/components/notification-box.vue';
import { apiHost } from '@/store/store.js';
import MainLayout from '@/layouts/main-layout.vue';
import { Head } from '@inertiajs/vue3';

const form = reactive({
  name: '',
  email: '',
  password: '',
  sex_id: '',
  rol_id: ''
});

const sexes = ref([]);
const loading = ref(false);
const message = ref('');
const isError = ref(false);

onMounted(async () => {
  try {
    const response = await axios.get(`${apiHost}sex/show-all`);
    sexes.value = response.data;
  } catch (error) {
    console.error('Error cargando sexos:', error);
  }
});

const handleSubmit = async () => {
  loading.value = true;
  message.value = '';
  isError.value = false;

  try {
    const { status } = await axios.post(`${apiHost}person/pollster-admin/create`, form);
    if (status === 201 || status === 200) {
      message.value = 'Usuario creado con éxito.';
      Object.assign(form, { name: '', email: '', password: '', sex_id: '', rol_id: '' });
    }
  } catch (error) {
    isError.value = true;
    message.value = error.response?.data?.message || 'Error al crear el usuario.';
  } finally {
    loading.value = false;
  }
};
</script>