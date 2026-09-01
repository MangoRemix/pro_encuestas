<template>
    <Head title="Gestión de Personal" />
    <MainLayout>
        <div class="max-w-7xl mx-auto p-0 md:p-6">
            <h1 class="text-2xl md:text-3xl text-slate-100 font-bold mb-6 text-center">Gestión de Personal</h1>

            <!-- Barra de herramientas -->
            <div class="flex flex-col md:flex-row justify-between gap-4 mb-6">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar por nombre, email o rol..."
                    class="w-full md:w-80 px-4 py-2 rounded bg-slate-900 border border-slate-700 text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 transition-all"
                />
                <div class="w-50">
                    <button
                    @click="isModalOpen = true"
                    class="flex items-center justify-center gap-x-2 px-4 py-2 rounded green-button-app cursor-pointer font-medium transition-colors"
                    >
                        <Icon class="text-2xl" icon="ic:outline-plus" />
                        Crear usuario
                    </button>
                </div>
                
        </div>

            <!-- Vista Móvil: Tarjetas -->
            <div class="md:hidden space-y-4">
                <div v-for="user in filteredStaff" :key="user.id" class="bg-slate-800 p-3 rounded-lg border border-slate-700 shadow-sm">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="font-bold text-white text-lg">{{ user.name }}</h3>
                        <span class="px-2 py-1 rounded text-xs bg-slate-800 border border-slate-700 text-blue-100">
                            {{ user.rol_id === 3 ? 'Admin' : 'Encuestador' }}
                        </span>
                    </div>
                    <div class="text-sm text-slate-400 space-y-1 mb-4">
                        <p>Email: {{ user.email }}</p>
                        <p>Sexo: {{ user.sex_id === 1 ? 'M' : 'F' }}</p>
                    </div>
                    <!-- Placeholder for actions if any were needed -->
                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-700">
                        <!-- Future implementation for edit/delete actions could go here -->
                    </div>
                </div>
            </div>

            <!-- Tabla con estilo Dashboard (Vista Escritorio) -->
            <div class="hidden md:block bg-gray-500/50 border border-slate-700 rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                    <thead>
                            <tr class="border-b border-slate-700 bg-slate-900/50 text-white text-xs uppercase tracking-wider">
                                <th class="p-4">Nombre</th>
                                <th class="p-4">Email</th>
                                <th class="p-4">Sexo</th>
                                <th class="p-4">Rol</th>
                        </tr>
                    </thead>
                        <tbody class="divide-y divide-slate-700/50 custom-scrollbar">
                            <tr v-for="user in filteredStaff" :key="user.id" class="text-slate-200 hover:bg-slate-600/30 transition-colors">
                                <td class="p-4">{{ user.name }}</td>
                                <td class="p-4">{{ user.email }}</td>
                                <td class="p-4">{{ user.sex_id === 1 ? 'M' : 'F' }}</td>
                                <td class="p-4">
                                    <span class="px-2 py-1 rounded text-xs bg-slate-800 border border-slate-700">
                                        {{ user.rol_id === 3 ? 'Admin' : 'Encuestador' }}
                                    </span>
                                </td>
                        </tr>
                    </tbody>
                </table>
            </div>
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
import { onMounted, ref, computed, watch } from 'vue';
import MainLayout from '@/layouts/main-layout.vue';
import Modal from '@/components/modal.vue';
import UserForm from '@/components/forms/user-form.vue';

onMounted(async ()=>{
    await getStaff()
});

const staff = ref([]);
const searchQuery = ref('');
const isModalOpen = ref(false);

const filteredStaff = computed(() => {
    const query = searchQuery.value.toLowerCase();
    if (!query) return staff.value;
    
    return staff.value.filter(user => {
        const roleName = user.rol_id === 3 ? 'admin' : 'encuestador';
        return (
            user.name.toLowerCase().includes(query) ||
            user.email.toLowerCase().includes(query) ||
            roleName.includes(query)
        );
    });
});

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
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #64748b; }
</style>
