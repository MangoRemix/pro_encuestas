<template>
    <Head title="Inicio de sesión"></Head>
    <MainLayout>
        
        <!-- Notificación flotante -->
        <div class="fixed top-5 right-5 z-50 w-80">
            <NotificationBox :message="errorMessage" :is-error="true" />
        </div>

        <div class="flex items-center justify-center w-[90%] sm:w-[80%] max-w-md bg-slate-700 backdrop-blur-md shadow-lg mx-auto rounded-xl mt-4 sm:mt-10 p-4 sm:p-6">
            <div class="w-full space-y-6">

                <div class="flex justify-center mb-8">
                    <div class="w-24 h-24 border-2 border-white rounded-full flex items-center justify-center">
                        <Icon icon="ic:round-person" class="w-12 h-12 text-white" />
                    </div>
                </div>

                <form @submit.prevent="login" class="space-y-4">
                    <div class="flex bg-white rounded-lg border border-gray-300 focus-within:ring-2 focus-within:ring-blue-500 overflow-hidden transition-all">
                        <div class="px-3 py-3 bg-gray-100 flex items-center border-r border-gray-300">
                            <Icon icon="ic:round-email" class="w-5 h-5 text-blue-900" />
                        </div>
                        <input v-model="form.email" type="email" placeholder="Correo electrónico" class="w-full px-4 py-3 bg-transparent focus:outline-none text-gray-900 placeholder-gray-500" required>
                    </div>

                    <div class="flex bg-white rounded-lg border border-gray-300 focus-within:ring-2 focus-within:ring-blue-500 overflow-hidden transition-all">
                        <div class="px-3 py-3 bg-gray-100 flex items-center border-r border-gray-300">
                            <Icon icon="ic:round-lock" class="w-5 h-5 text-blue-900" />
                        </div>
                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="Contraseña"
                            class="w-full px-4 py-3 bg-transparent focus:outline-none text-gray-900 placeholder-gray-500"
                            required
                        >
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="px-3 flex items-center text-gray-500 hover:text-blue-900 focus:outline-none transition-colors cursor-pointer"
                        >
                            <Icon :icon="showPassword ? 'ic:round-visibility-off' : 'ic:round-visibility'" class="w-5 h-5" />
                        </button>
                    </div>
                    <button
                        class="w-full py-3 font-bold uppercase tracking-wider text-white rounded-lg transition-all duration-200"
                        :class="disabledLoginButton ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-900 hover:bg-blue-800 shadow-md hover:shadow-lg'"
                        :disabled="disabledLoginButton"
                    >
                        Entrar
                    </button>
                </form>

                <div class="flex flex-col sm:flex-row justify-between items-center text-sm text-white gap-3">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input v-model="form.remember" type="checkbox" class="rounded border-gray-300 text-blue-900 focus:ring-blue-900">
                        <span>Recordarme</span>
                    </label>
                    <a href="#" class="text-white hover:underline">¿Olvidaste tu contraseña?</a>
                </div>
            </div>
        </div>

    </MainLayout>
</template>
<script setup>
import MainLayout from '@/layouts/main-layout.vue';
import NotificationBox from '@/components/notification-box.vue'; // Importar el componente
import { Icon } from '@iconify/vue';
import { apiHost } from '@/store/store';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, watch } from 'vue';

const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
const errorMessage = ref(''); // Estado para el mensaje de error

const form = useForm({
    email: '',
    password: '',
    remember: false
});

const showPassword = ref(false);
const disabledLoginButton = ref(true);

const login = async () => {
    errorMessage.value = ''; // Limpiar error previo
    try {
        await axios.post('login', {
            email: form.email,
            password: form.password,
            remember: form.remember
        });
        window.location.href = '/';
    } catch (error) {
        if (error.response?.status === 422) {
            errorMessage.value = 'Credenciales incorrectas o datos inválidos.';
        } else {
            errorMessage.value = 'Ocurrió un error al intentar iniciar sesión.';
        }

        // Auto-ocultar después de 4 segundos
        setTimeout(() => {
            errorMessage.value = '';
        }, 4000);
    }
};

watch(
    () => [form.email, form.password],
    ([email, password]) => {
        
        if (password.length > 7 && emailRegex.test(email)) {
            disabledLoginButton.value = false;
        } else {
            disabledLoginButton.value = true;
        }
    }
);

</script>
<style>
    
</style>