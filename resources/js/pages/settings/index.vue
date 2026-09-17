<template>
    <Head title="Configuración general" />
    <MainLayout>
        <div class="mx-auto max-w-2xl px-4 py-10">
            <div
                class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xl sm:p-8"
            >
                <div class="mb-6 text-center">
                    <h2
                        class="text-2xl font-bold tracking-tight text-slate-800"
                    >
                        Configuración general
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Actualiza tus datos de cuenta
                    </p>
                </div>

                <form @submit.prevent="handleSubmit" class="space-y-4">
                    <div class="flex flex-col gap-1.5">
                        <label
                            for="name"
                            class="text-sm font-medium text-slate-700"
                            >Nombre Completo</label
                        >
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            autocomplete="name"
                            required
                            class="inputs-form w-full rounded-lg border-slate-300 text-sm transition-colors focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label
                            for="email"
                            class="text-sm font-medium text-slate-700"
                            >Correo Electrónico</label
                        >
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            required
                            class="inputs-form w-full rounded-lg border-slate-300 text-sm transition-colors focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>

                    <hr class="my-2 border-slate-200" />

                    <p class="text-sm font-medium text-slate-600">
                        Cambiar contraseña (opcional)
                    </p>

                    <div class="flex flex-col gap-1.5">
                        <label
                            for="current_password"
                            class="text-sm font-medium text-slate-700"
                            >Contraseña Actual</label
                        >
                        <input
                            id="current_password"
                            v-model="form.current_password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="Solo si vas a cambiar la contraseña"
                            class="inputs-form w-full rounded-lg border-slate-300 text-sm transition-colors focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label
                            for="password"
                            class="text-sm font-medium text-slate-700"
                            >Nueva Contraseña</label
                        >
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            autocomplete="new-password"
                            placeholder="Dejar en blanco para no cambiarla"
                            class="inputs-form w-full rounded-lg border-slate-300 text-sm transition-colors focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>

                    <div class="pt-2">
                        <button
                            type="submit"
                            :disabled="loading"
                            class="primary-button-app flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 font-semibold text-white shadow-sm transition-all duration-150 hover:bg-indigo-700 active:bg-indigo-800 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{ loading ? 'Guardando...' : 'Guardar Cambios' }}
                        </button>
                    </div>
                </form>

                <div class="mt-4">
                    <NotificationBox
                        v-if="message"
                        :message="message"
                        :isError="isError"
                    />
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { reactive, ref } from 'vue';
import NotificationBox from '@/components/notification-box.vue';
import MainLayout from '@/layouts/main-layout.vue';
import { apiHost } from '@/store/store.js';

const page = usePage();

const form = reactive({
    name: page.props.auth.user.name,
    email: page.props.auth.user.email,
    current_password: '',
    password: '',
});

const loading = ref(false);
const message = ref('');
const isError = ref(false);

const handleSubmit = async () => {
    loading.value = true;
    message.value = '';
    isError.value = false;

    try {
        const { data } = await axios.put(`${apiHost}profile`, form);
        message.value = data.message;
        form.current_password = '';
        form.password = '';
    } catch (error) {
        isError.value = true;
        message.value =
            error.response?.data?.message ||
            'Ocurrió un error al actualizar el perfil.';
    } finally {
        loading.value = false;
        setTimeout(() => {
            message.value = '';
        }, 3500);
    }
};
</script>
