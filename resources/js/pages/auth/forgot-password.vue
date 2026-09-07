<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Icon } from "@iconify/vue";
import MainLayout from '@/layouts/main-layout.vue';
import NotificationBox from '@/components/notification-box.vue';

// Props passed from the backend (e.g., session status message)
const props = defineProps({
    status: {
        type: String,
        default: null,
    },
    errors: {
        type: Object,
        default: () => ({})
    }
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/forgot-password');
};

// Focus on the email input when the component is mounted
onMounted(() => {
    const emailInput = document.querySelector('input[type="email"]');
    if (emailInput) {
        emailInput.focus();
    }
});
</script>

<template>
    <Head title="¿Olvidaste tu contraseña?" />
    <MainLayout>
        <div class="flex items-center justify-center w-[90%] sm:w-[80%] max-w-md bg-slate-700 backdrop-blur-md shadow-lg mx-auto rounded-xl mt-4 sm:mt-10 p-4 sm:p-6">
            <div class="w-full space-y-6">
                <div class="flex justify-center mb-8">
                    <div class="w-24 h-24 border-2 border-white rounded-full flex items-center justify-center">
                        <Icon icon="ic:round-lock-reset" class="w-12 h-12 text-white" />
                    </div>
                </div>

                <!-- Status message if email was sent -->
                <NotificationBox v-if="status" :message="status" :is-error="false" />

                <form @submit.prevent="submit" class="space-y-4">
                    <div class="flex bg-white rounded-lg border border-gray-300 focus-within:ring-2 focus-within:ring-blue-500 overflow-hidden transition-all">
                        <div class="px-3 py-3 bg-gray-100 flex items-center border-r border-gray-300">
                            <Icon icon="ic:round-email" class="w-5 h-5 text-blue-900" />
                        </div>
                        <input v-model="form.email" type="email" placeholder="Correo electrónico" class="w-full px-4 py-2.5 text-sm bg-transparent focus:outline-none text-gray-900 placeholder-gray-500" required autofocus>
                    </div>

                    <!-- Display validation errors -->
                    <NotificationBox v-if="form.errors.email" :message="form.errors.email" :is-error="true" />

                    <div class="flex flex-col items-center justify-center mt-6 gap-3">
                        <button
                            type="submit"
                            class="py-2.5 px-5 text-xs font-semibold uppercase tracking-wider text-white rounded-lg transition-all duration-200"
                            :class="form.processing ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-900 hover:bg-blue-800 shadow-md hover:shadow-lg'"
                            :disabled="form.processing"
                        >
                            Enviar enlace de restablecimiento
                        </button>
                        <Link href="/login" class="text-xs text-gray-300 hover:text-white">Volver al inicio de sesión</Link>
                    </div>
                </form>
            </div>
        </div>
    </MainLayout>
</template>
