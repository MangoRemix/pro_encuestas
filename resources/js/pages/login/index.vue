<template>
    <Head title="Inicio de sesión" />
    <MainLayout>
        
        <!-- Notification box is handled by form.errors now -->
        <div v-if="$page.props.flash?.status" class="fixed top-5 right-5 z-50 w-80">
            <NotificationBox :message="$page.props.flash.status" :is-error="false" />
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

                    <!-- Display validation errors for email -->
                    <div v-if="form.errors.email" class="text-red-500 text-sm">
                        {{ form.errors.email }}
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
                    
                    <!-- Display validation errors for password -->
                    <div v-if="form.errors.password" class="text-red-500 text-sm">
                        {{ form.errors.password }}
                    </div>

                    <button
                        class="w-full py-3 font-bold uppercase tracking-wider text-white rounded-lg transition-all duration-200"
                        :class="form.processing ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-900 hover:bg-blue-800 shadow-md hover:shadow-lg'"
                        :disabled="form.processing"
                    >
                        Entrar
                    </button>
                </form>

                <div class="flex flex-col sm:flex-row justify-between items-center text-sm text-white gap-3">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input v-model="form.remember" type="checkbox" class="rounded border-gray-300 text-blue-900 focus:ring-blue-900">
                        <span>Recordarme</span>
                    </label>
                    <Link href="/forgot-password" class="text-white hover:underline">¿Olvidaste tu contraseña?</Link>
                </div>
            </div>
        </div>

    </MainLayout>
</template>

<script setup>
import { Icon } from '@iconify/vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import NotificationBox from '@/components/notification-box.vue';
import MainLayout from '@/layouts/main-layout.vue';

const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
// const errorMessage = ref(''); // errorMessage is replaced by form.errors

const form = useForm({
    email: '',
    password: '',
    remember: false
});

const showPassword = ref(false);
// disabledLoginButton is now managed by form.processing
// const disabledLoginButton = ref(true);

const login = () => {
    // Use Inertia's useForm post method for submission
    form.post('/login', {
        preserveState: true, // Preserve form state on submission
        onSuccess: () => {
            // Inertia handles redirects automatically based on backend response
        },
        onError: (errors) => {
            // Error messages are automatically available in form.errors
            console.error('Login errors:', errors);
        }
    });
};

// Watch for changes in form fields to enable/disable the login button
watch(
    () => [form.email, form.password],
    ([email, password]) => {
        // Enable button if email is valid and password has minimum length
        if (password.length >= 8 && emailRegex.test(email)) {
            // disabledLoginButton.value = false;
        } else {
            // disabledLoginButton.value = true;
        }
    }
);

</script>

<style>
    
</style>
