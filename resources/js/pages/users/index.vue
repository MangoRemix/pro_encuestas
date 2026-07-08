<template>
    <Head title="Gestión de Personal" />
    <MainLayout>
        <div class="text-center">
            <h2 class="text-3xl text-white font-bold mt-8">Usuarios</h2>
        </div>

        <div class="w-full flex justify-end mb-3">
            <button @click="isModalOpen = true" class="flex items-center rounded-full text-white bg-yellow-400 cursor-pointer hover:bg-yellow-300 h-9 w-40 p-2 font-bold">
                <Icon class="text-2xl" icon="ic:outline-plus" />
                Crear usuario
            </button>
        </div>

        <div class="bg-white/30 backdrop-blur-md shadow-lg rounded-xl p-6 border border-blue-700/50 w-full h-135 mt-6">
            <div id="table-header" class="h-10 w-full mb-3">
                <table class="table-fixed w-full text-left">
                    <thead>
                        <tr class="border-b border-white/30 text-white text-lg">
                            <th class="p-2 w-1/3">Nombre</th>
                            <th class="p-2 w-1/3">Email</th>
                            <th class="p-2 w-1/6">Sexo</th>
                            <th class="p-2 w-1/6">Rol</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div id="table-body" class="w-full max-h-100 overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30">
                <table class="table-fixed w-full">
                    <tbody>
                        <tr v-for="user in staff" :key="user.id" class="text-white border-b border-neutral-400">
                            <td class="py-3 px-2 w-1/3 truncate">{{ user.name }}</td>
                            <td class="py-3 px-2 w-1/3 truncate">{{ user.email }}</td>
                            <td class="py-3 px-2 w-1/6">{{ user.sex_id === 1 ? 'M' : 'F' }}</td>
                            <td class="py-3 px-2 w-1/6">{{ user.rol_id === 3 ? 'Admin' : 'Encuestador' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Modal :show="isModalOpen" @close="isModalOpen = false">
            <UserForm class="w-100" @created="handleUserCreated" />
        </Modal>
    </MainLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { Icon } from "@iconify/vue";
import axios from 'axios';
import { apiHost } from '../../store/store';
import { onMounted, ref } from 'vue';
import MainLayout from '@/layouts/main-layout.vue';
import Modal from '@/components/modal.vue';
import UserForm from '@/components/forms/user-form.vue';

const staff = ref([]);
const isModalOpen = ref(false);

const getStaff = async () => {
    try {
        const response = await axios.get(`${apiHost}person/pollster-admin/list`);
        staff.value = response.data || [];
    } catch (error) {
        console.error("Error al cargar personal:", error);
    }
};

const handleUserCreated = () => {
    isModalOpen.value = false;
    getStaff();
};

onMounted(getStaff);
</script>