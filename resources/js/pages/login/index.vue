<template>
    <Head title="Inicio de sesión"></Head>
    <MainLayout>
        
        <!-- Notificación flotante -->
        <div class="fixed top-5 right-5 z-50 w-80">
                <NotificationBox :message="errorMessage" :is-error="true" />
                    </div>

        <div class="flex items-center justify-center min-h-125 bg-white/30 backdrop-blur-md shadow-lg w-[95%] sm:w-100 md:w-112.5 mx-auto rounded-xl mt-10">
            <div class="w-full p-6 space-y-6">

                <div class="flex justify-center mb-8">
                    <div class="w-24 h-24 border-2 border-white rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                    </div>

                <form @submit.prevent="login" class="space-y-4">
                    <div class="flex bg-white rounded-lg border border-gray-300 focus-within:ring-2 focus-within:ring-blue-500 overflow-hidden transition-all">
                        <div class="px-3 py-3 bg-gray-100 flex items-center border-r border-gray-300">
                            <svg class="w-5 h-5 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <input v-model="form.email" type="email" placeholder="Correo electrónico" class="w-full px-4 py-3 bg-transparent focus:outline-none text-gray-900 placeholder-gray-500" required>
                    </div>

                    <div class="flex bg-white rounded-lg border border-gray-300 focus-within:ring-2 focus-within:ring-blue-500 overflow-hidden transition-all">
                        <div class="px-3 py-3 bg-gray-100 flex items-center border-r border-gray-300">
                            <svg class="w-5 h-5 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <input v-model="form.password" type="password" placeholder="Contraseña" class="w-full px-4 py-3 bg-transparent focus:outline-none text-gray-900 placeholder-gray-500" required>
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

const disabledLoginButton = ref(true)

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