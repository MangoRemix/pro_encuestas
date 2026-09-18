<template>
    <Head title="Inicio de sesión" />
    <MainLayout>
        <!-- Notification box is handled by form.errors now -->
        <div
            v-if="$page.props.flash?.status"
            class="fixed top-5 right-5 z-50 w-80"
        >
            <NotificationBox
                :message="$page.props.flash.status"
                :is-error="false"
            />
        </div>

        <div
            class="mx-auto mt-4 flex w-[90%] max-w-md items-center justify-center rounded-xl bg-slate-700 p-4 shadow-lg backdrop-blur-md sm:mt-10 sm:w-[80%] sm:p-6"
        >
            <div class="w-full space-y-6">
                <div class="mb-8 flex justify-center">
                    <div
                        class="flex h-24 w-24 items-center justify-center rounded-full border-2 border-white"
                    >
                        <Icon
                            icon="ic:round-person"
                            class="h-12 w-12 text-white"
                        />
                    </div>
                </div>

                <form @submit.prevent="login" class="space-y-4">
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
                            class="w-full bg-transparent px-4 py-3 text-gray-900 placeholder-gray-500 focus:outline-none"
                            required
                        />
                    </div>

                    <!-- Display validation errors for email -->
                    <div v-if="form.errors.email" class="text-sm text-red-500">
                        {{ form.errors.email }}
                    </div>

                    <div
                        class="flex overflow-hidden rounded-lg border border-gray-300 bg-white transition-all focus-within:ring-2 focus-within:ring-blue-500"
                    >
                        <div
                            class="flex items-center border-r border-gray-300 bg-gray-100 px-3 py-3"
                        >
                            <Icon
                                icon="ic:round-lock"
                                class="h-5 w-5 text-blue-900"
                            />
                        </div>
                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="Contraseña"
                            class="w-full bg-transparent px-4 py-3 text-gray-900 placeholder-gray-500 focus:outline-none"
                            required
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="flex cursor-pointer items-center px-3 text-gray-500 transition-colors hover:text-blue-900 focus:outline-none"
                        >
                            <Icon
                                :icon="
                                    showPassword
                                        ? 'ic:round-visibility-off'
                                        : 'ic:round-visibility'
                                "
                                class="h-5 w-5"
                            />
                        </button>
                    </div>

                    <!-- Display validation errors for password -->
                    <div
                        v-if="form.errors.password"
                        class="text-sm text-red-500"
                    >
                        {{ form.errors.password }}
                    </div>

                    <button
                        class="w-full rounded-lg py-3 font-bold tracking-wider text-white uppercase transition-all duration-200"
                        :class="
                            form.processing
                                ? 'cursor-not-allowed bg-gray-400'
                                : 'bg-blue-900 shadow-md hover:bg-blue-800 hover:shadow-lg'
                        "
                        :disabled="form.processing"
                    >
                        Entrar
                    </button>
                </form>

                <div class="flex items-center gap-3 text-sm text-white">
                    <label class="flex cursor-pointer items-center space-x-2">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-900 focus:ring-blue-900"
                        />
                        <span>Recordarme</span>
                    </label>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { Icon } from '@iconify/vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import NotificationBox from '@/components/notification-box.vue';
import MainLayout from '@/layouts/main-layout.vue';

const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
// const errorMessage = ref(''); // errorMessage is replaced by form.errors

const form = useForm({
    email: '',
    password: '',
    remember: false,
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
        },
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
    },
);
</script>

<style></style>
