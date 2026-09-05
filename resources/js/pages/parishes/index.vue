<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Icon } from "@iconify/vue";
import { useParishes } from '@/composables/api/parishes';
import MainLayout from '@/layouts/main-layout.vue';
import Modal from '@/components/modal.vue';

const { parishes, fetchParishes, storeParish, updateParish, deleteParish } = useParishes();

const isModalOpen = ref(false);
const editingParish = ref(null);
const form = ref({ name: '' });
const errors = ref({});

onMounted(fetchParishes);

const openModal = (parish = null) => {
    editingParish.value = parish;
    form.value = parish ? { ...parish } : { name: '' };
    errors.value = {};
    isModalOpen.value = true;
};

const save = async () => {
    try {
        if (editingParish.value) {
            await updateParish(editingParish.value.id, form.value);
        } else {
            await storeParish(form.value);
        }
        isModalOpen.value = false;
        fetchParishes();
    } catch (e) {
        if (e.response?.data?.errors) errors.value = e.response.data.errors;
    }
};

const remove = async (id) => {
    if (confirm('¿Eliminar esta parroquia?')) {
        await deleteParish(id);
        await fetchParishes();
    }
};
</script>

<template>
    <Head title="Gestión de Parroquias" />
    <MainLayout>
        <div class="text-center">
            <h2 class="text-3xl text-white font-bold mt-8 underline">Gestión de Parroquias</h2>
        </div>

        <div class="flex items-center justify-end mb-6">
            <div class="w-full sm:w-fit">

                <button @click="openModal()" class="green-button-app flex items-center gap-x-2 justify-center cursor-pointer">
                    <Icon class="text-2xl" icon="ic:outline-plus" />
                    Nueva Parroquia
                </button>
            </div>
        </div>

        <div class="bg-gray-500/50 border border-slate-700 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-700 bg-slate-900/50 text-white text-xs uppercase tracking-wider">
                            <th class="p-4">Nombre</th>
                            <th class="p-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50 custom-scrollbar">
                        <tr v-for="p in parishes" :key="p.id" class="text-slate-200 hover:bg-slate-600/30 transition-colors">
                            <td class="p-4 font-medium">{{ p.name }}</td>
                            <td class="p-4">
                                <div class="flex gap-3 justify-center">
                                    <Icon @click="openModal(p)" class="text-xl text-yellow-500 hover:text-yellow-400 cursor-pointer" icon="ic:baseline-edit"/>
                                    <Icon @click="remove(p.id)" class="text-xl text-red-500 hover:text-red-400 cursor-pointer" icon="ic:baseline-restore-from-trash"/>
            </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Modal :show="isModalOpen" @close="isModalOpen = false">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-4 text-slate-800">{{ editingParish ? 'Editar' : 'Crear' }} Parroquia</h2>
                <input v-model="form.name" class="inputs-form w-full mb-2" placeholder="Nombre de la parroquia" />
                <p v-if="errors.name" class="text-red-500 text-sm">{{ errors.name[0] }}</p>

                <div class="mt-6 flex justify-end gap-3">
                    <button @click="isModalOpen = false" class="px-4 py-2 text-slate-600 hover:text-slate-800">Cancelar</button>
                    <button @click="save" class="primary-button-app">Guardar</button>
                </div>
            </div>
        </Modal>
    </MainLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #64748b; }
</style>

