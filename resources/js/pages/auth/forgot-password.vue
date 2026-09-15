<script setup>
import { Icon } from '@iconify/vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import NotificationBox from '@/components/notification-box.vue';
import MainLayout from '@/layouts/main-layout.vue';

// Props passed from the backend (e.g., session status message)
defineProps({
    status: {
        type: String,
        default: null,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
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
        <div
            class="mx-auto mt-4 flex w-[90%] max-w-md items-center justify-center rounded-xl bg-slate-700 p-4 shadow-lg backdrop-blur-md sm:mt-10 sm:w-[80%] sm:p-6"
        >
            <div class="w-full space-y-6">
                <div class="mb-8 flex justify-center">
                    <div
                        class="flex h-24 w-24 items-center justify-center rounded-full border-2 border-white"
                    >
                        <Icon
                            icon="ic:round-lock-reset"
                            class="h-12 w-12 text-white"
                        />
                    </div>
                </div>

                <!-- Status message if email was sent -->
                <NotificationBox
                    v-if="status"
                    :message="status"
                    :is-error="false"
                />

                <form @submit.prevent="submit" class="space-y-4">
                    <div
                        class="flex overflow-hidden rounded-lg border border-gray-300 bg-white transition-all focus-within:ring-2 focus-within:ring-blue-500"
                    >
                        <div
                            class="flex items-center border-r border-gray-300 bg-gray-100 px-3 py-3"
                        >
                            <Icon
                                icon="ic:round-email"
                                class="h-5 w-5 text-blue-900"
                            />
                        </div>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="Correo electrónico"
                            class="w-full bg-transparent px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:outline-none"
                            required
                            autofocus
                        />
                    </div>

                    <!-- Display validation errors -->
                    <NotificationBox
                        v-if="form.errors.email"
                        :message="form.errors.email"
                        :is-error="true"
                    />

                    <div
                        class="mt-6 flex flex-col items-center justify-center gap-3"
                    >
                        <button
                            type="submit"
                            class="rounded-lg px-5 py-2.5 text-xs font-semibold tracking-wider text-white uppercase transition-all duration-200"
                            :class="
                                form.processing
                                    ? 'cursor-not-allowed bg-gray-400'
                                    : 'bg-blue-900 shadow-md hover:bg-blue-800 hover:shadow-lg'
                            "
                            :disabled="form.processing"
                        >
                            Enviar enlace de restablecimiento
                        </button>
                        <Link
                            href="/login"
                            class="text-xs text-gray-300 hover:text-white"
                            >Volver al inicio de sesión</Link
                        >
                    </div>
                </form>
            </div>
        </div>
    </MainLayout>
</template>
