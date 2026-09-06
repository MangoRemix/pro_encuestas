<template>
  <div class="w-full max-w-lg mx-auto bg-white rounded-2xl shadow-xl border border-slate-100 p-6 sm:p-4">
    <div class="text-center mb-6">
      <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Registrar Usuario</h2>
      <p class="text-sm text-slate-500 mt-1">Completa los datos para crear un nuevo usuario en el sistema</p>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div class="flex flex-col gap-1.5">
        <label for="name" class="text-sm font-medium text-slate-700">Nombre Completo</label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          autocomplete="name"
          placeholder="Ej. Juan Pérez"
          required
          class="inputs-form w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm transition-colors"
        />
      </div>

      <div class="flex flex-col gap-1.5">
        <label for="email" class="text-sm font-medium text-slate-700">Correo Electrónico</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          autocomplete="email"
          placeholder="usuario@ejemplo.com"
          required
          class="inputs-form w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm transition-colors"
        />
      </div>

      <div class="flex flex-col gap-1.5">
        <label for="password" class="text-sm font-medium text-slate-700">Contraseña</label>
        <div class="relative">
          <input
            id="password"
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            autocomplete="new-password"
            placeholder="••••••••"
            required
            class="inputs-form w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm pr-10 transition-colors"
          />
          <button
            type="button"
            @click="showPassword = !showPassword"
            tabindex="-1"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none"
          >
            <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
            </svg>
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="flex flex-col gap-1.5">
          <label for="sex_id" class="text-sm font-medium text-slate-700">Sexo</label>
          <select
            id="sex_id"
            v-model="form.sex_id"
            required
            class="inputs-form w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm transition-colors"
          >
            <option value="" disabled>Seleccione...</option>
            <option v-for="sex in sexes" :key="sex.id" :value="sex.id">{{ sex.abbreviation }}</option>
          </select>
        </div>

        <div class="flex flex-col gap-1.5">
          <label for="rol_id" class="text-sm font-medium text-slate-700">Rol</label>
          <select
            id="rol_id"
            v-model="form.rol_id"
            required
            class="inputs-form w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm transition-colors"
          >
            <option value="" disabled>Seleccione...</option>
            <option :value="1">Encuestador</option>
            <option :value="3">Administrador</option>
          </select>
        </div>
      </div>

      <div class="pt-2">
        <button
          type="submit"
          :disabled="loading"
          :aria-busy="loading"
          class="primary-button-app w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-lg font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:opacity-60 disabled:cursor-not-allowed transition-all duration-150 shadow-sm cursor-pointer"
        >
          <svg v-if="loading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
          </svg>
          <span>{{ loading ? 'Guardando...' : 'Crear Usuario' }}</span>
        </button>
      </div>
    </form>

    <div class="mt-4">
      <NotificationBox v-if="message" :message="message" :isError="isError" />
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import NotificationBox from '@/components/notification-box.vue';
import { apiHost } from '@/store/store.js';

const emit = defineEmits(['created']);
const form = reactive({ name: '', email: '', password: '', sex_id: '', rol_id: '' });
const sexes = ref([]);
const loading = ref(false);
const showPassword = ref(false);
const message = ref('');
const isError = ref(false);

onMounted(async () => {
  try {
    const { data } = await axios.get(`${apiHost}sex/show-all`);
    sexes.value = data;
  } catch (error) {
    console.error('Error cargando sexos:', error);
  }
});

const handleSubmit = async () => {
  loading.value = true;
  message.value = '';
  try {
    await axios.post(`${apiHost}person/pollster-admin/create`, form);
    emit('created');
  } catch (error) {
    isError.value = true;
    message.value = error.response?.data?.message || 'Error al crear el usuario.';
  } finally {
    loading.value = false;
  }
};
</script>
