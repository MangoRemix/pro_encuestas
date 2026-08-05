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

        <div class="w-full flex justify-end items-center mb-3">
            <button @click="openModal()" class="flex items-center rounded-full text-white bg-yellow-400 cursor-pointer hover:bg-yellow-300 h-9 w-fit p-2 font-bold justify-center">
                <Icon class="text-2xl" icon="ic:outline-plus" />
                Nueva Parroquia
            </button>
        </div>

        <div class="bg-white/30 backdrop-blur-md shadow-lg rounded-xl p-6 border border-blue-700/50 w-full">
            <div id="table-header" class="h-10 w-full mb-3">
                <table class="table-fixed w-full text-left">
                    <thead>
                        <tr class="border-b border-white/30 text-white text-lg">
                            <th class="p-2">Nombre</th>
                            <th class="p-2 text-center w-40">Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div id="table-body" class="w-full max-h-100 overflow-y-auto">
                <table class="table-fixed w-full">
                    <tbody class="text-white">
                        <tr v-for="p in parishes" :key="p.id" class="border-b border-neutral-400">
                            <td class="py-3 px-2">{{ p.name }}</td>
                            <td class="py-3 px-2 text-center w-40">
                                <div class="flex gap-x-3 justify-center">
                                    <Icon @click="openModal(p)" class="text-2xl text-yellow-600 hover:text-yellow-500 cursor-pointer" icon="ic:baseline-edit"/>
                                    <Icon @click="remove(p.id)" class="text-2xl text-red-600 hover:text-red-500 cursor-pointer" icon="ic:baseline-restore-from-trash"/>
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
                    <button @click="save" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Guardar</button>
                </div>
            </div>
        </Modal>
    </MainLayout>
</template>

