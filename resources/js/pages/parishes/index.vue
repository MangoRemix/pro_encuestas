<script setup>
import { Icon } from '@iconify/vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import Modal from '@/components/modal.vue';
import { useParishes } from '@/composables/api/parishes';
import MainLayout from '@/layouts/main-layout.vue';

const { parishes, fetchParishes, storeParish, updateParish, deleteParish } =
    useParishes();

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
        if (e.response?.data?.errors) {
            errors.value = e.response.data.errors;
        }
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
            <h2 class="mt-8 text-3xl font-bold text-white underline">
                Gestión de Parroquias
            </h2>
        </div>

        <div class="mb-6 flex items-center justify-end">
            <div class="w-full sm:w-fit">
                <button
                    @click="openModal()"
                    class="green-button-app flex cursor-pointer items-center justify-center gap-x-2"
                >
                    <Icon class="text-2xl" icon="ic:outline-plus" />
                    Nueva Parroquia
                </button>
            </div>
        </div>

        <div
            class="overflow-hidden rounded-lg border border-slate-700 bg-gray-500/30"
        >
            <div class="custom-scrollbar max-h-110 overflow-y-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr
                            class="sticky top-0 z-10 border-b border-slate-700 bg-slate-900 text-xs tracking-wider text-white uppercase"
                        >
                            <th class="p-4">Nombre</th>
                            <th class="p-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        <tr
                            v-for="p in parishes"
                            :key="p.id"
                            class="text-slate-200 transition-colors hover:bg-slate-600/30"
                        >
                            <td class="p-4 font-medium">{{ p.name }}</td>
                            <td class="p-4">
                                <div class="flex justify-center gap-3">
                                    <Icon
                                        @click="openModal(p)"
                                        class="cursor-pointer text-xl text-yellow-500 hover:text-yellow-400"
                                        icon="ic:baseline-edit"
                                    />
                                    <Icon
                                        @click="remove(p.id)"
                                        class="cursor-pointer text-xl text-red-500 hover:text-red-400"
                                        icon="ic:baseline-restore-from-trash"
                                    />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Modal :show="isModalOpen" @close="isModalOpen = false">
            <div class="p-6">
                <h2 class="mb-4 text-xl font-bold text-slate-800">
                    {{ editingParish ? 'Editar' : 'Crear' }} Parroquia
                </h2>
                <input
                    v-model="form.name"
                    class="inputs-form mb-2 w-full"
                    placeholder="Nombre de la parroquia"
                />
                <p v-if="errors.name" class="text-sm text-red-500">
                    {{ errors.name[0] }}
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        @click="isModalOpen = false"
                        class="px-4 py-2 text-slate-600 hover:text-slate-800"
                    >
                        Cancelar
                    </button>
                    <button @click="save" class="primary-button-app">
                        Guardar
                    </button>
                </div>
            </div>
        </Modal>
    </MainLayout>
</template>

<style scoped>
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #64748b #0f172a;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #0f172a;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #64748b;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
