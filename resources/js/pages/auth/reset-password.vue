<script setup>
import { Icon } from '@iconify/vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import NotificationBox from '@/components/notification-box.vue';
import MainLayout from '@/layouts/main-layout.vue';

// Props passed from the backend
const props = defineProps({
    token: {
        type: String,
        required: true,
    },
    email: {
        type: String,
        default: null,
    },
    status: {
        type: String,
        default: null,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const showPassword = ref(false);

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/reset-password');
};
</script>

<template>
    <Head title="Restablecer contraseña" />
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

                <!-- Status message if password was reset -->
                <NotificationBox
                    v-if="status"
                    :message="status"
                    :is-error="false"
                />

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Email Field -->
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
                        />
                    </div>

                    <!-- Password Input -->
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
                            placeholder="Nueva contraseña"
                            class="w-full bg-transparent px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:outline-none"
                            required
                            autocomplete="new-password"
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

                    <!-- Password Confirmation Input -->
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
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="Confirmar contraseña"
                            class="w-full bg-transparent px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:outline-none"
                            required
                            autocomplete="new-password"
                        />
                    </div>

                    <!-- Display validation errors -->
                    <NotificationBox
                        v-if="
                            form.errors.password ||
                            form.errors.email ||
                            form.errors.token
                        "
                        :message="
                            form.errors.password ||
                            form.errors.email ||
                            form.errors.token
                        "
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
                            Restablecer contraseña
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
