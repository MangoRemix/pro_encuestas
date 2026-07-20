<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Icon } from "@iconify/vue";
import MainLayout from '@/layouts/main-layout.vue';
import Modal from '@/components/modal.vue';
import { getAgeRanges, saveAgeRange, deleteAgeRange } from '@/composables/api/ageRanges';

const ranges = ref([]);
const isModalOpen = ref(false);
const form = ref({ id: null, init_range: '', finish_range: '' });

const loadData = async () => {
    const res = await getAgeRanges();
    if (!res.errorFlag) ranges.value = res.data;
};

const openModal = (item = null) => {
    if (item) {
        const [init, finish] = item.range.split(' - ');
        form.value = { id: item.id, init_range: init, finish_range: finish };
    } else {
        form.value = { id: null, init_range: '', finish_range: '' };
    }
    isModalOpen.value = true;
};

const submit = async () => {
    const res = await saveAgeRange(form.value, form.value.id);
    if (!res.errorFlag) {
        isModalOpen.value = false;
        loadData();
    } else {
        alert(res.responseMessage);
    }
};

const remove = async (id) => {
    if (confirm('¿Estás seguro de eliminar este rango?')) {
        await deleteAgeRange(id);
        loadData();
    }
};

onMounted(loadData);
</script>

<template>
    <Head title="Rangos de Edad" />
    <MainLayout>
        <div class="text-center">
            <h2 class="text-3xl text-white font-bold mt-8 underline">Rangos de Edad</h2>
        </div>

        <div class="w-full flex justify-end mb-3">
            <button @click="openModal()" class="flex items-center rounded-full text-white bg-yellow-400 cursor-pointer hover:bg-yellow-300 h-9 w-40 p-2 font-bold justify-center">
                <Icon class="text-2xl" icon="ic:outline-plus" />
                Nuevo Rango
            </button>
        </div>

        <div class="bg-white/30 backdrop-blur-md shadow-lg rounded-xl p-6 border border-blue-700/50 w-1/2 mx-auto">
            <table class="table-fixed w-full text-left">
                <thead>
                    <tr class="border-b border-white/30 text-white text-lg">
                        <th class="p-2">Rango</th>
                        <th class="p-2 text-center w-30">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in ranges" :key="item.id" class="text-white border-b border-neutral-400">
                        <td class="py-3">{{ item.range }}</td>
                        <td class="py-3 text-center">
                            <div class="flex gap-x-3 justify-center">
                                <Icon @click="openModal(item)" class="text-2xl text-yellow-600 hover:text-yellow-500 cursor-pointer" icon="ic:baseline-edit"/>
                                <Icon @click="remove(item.id)" class="text-2xl text-red-600 hover:text-red-500 cursor-pointer" icon="ic:baseline-restore-from-trash"/>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Modal :show="isModalOpen" @close="isModalOpen = false;">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-5">{{ form.id ? 'Editar' : 'Crear' }} Rango</h2>
                <input v-model="form.init_range" type="number" placeholder="Edad Inicial" class="w-full mb-3 p-2 rounded">
                <input v-model="form.finish_range" type="number" placeholder="Edad Final" class="w-full mb-3 p-2 rounded">
                <button @click="submit" class="bg-yellow-400 text-white w-full py-2 rounded font-bold">Guardar</button>
            </div>
        </Modal>
    </MainLayout>
</template>



