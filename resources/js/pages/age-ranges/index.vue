<script setup>
import { ref, onMounted } from 'vue';
import { getAgeRanges, saveAgeRange, deleteAgeRange } from '@/composables/api/ageRanges';

const ranges = ref([]);
const showModal = ref(false);
const form = ref({ id: null, init_range: '', finish_range: '' });

const loadData = async () => {
    const res = await getAgeRanges();
    console.log(res)
    if (!res.errorFlag) ranges.value = res.data;
};

const openModal = (item = null) => {
    if (item) {
        const [init, finish] = item.range.split(' - ');
        form.value = { id: item.id, init_range: init, finish_range: finish };
    } else {
        form.value = { id: null, init_range: '', finish_range: '' };
    }
    showModal.value = true;
};

const submit = async () => {
    const res = await saveAgeRange(form.value, form.value.id);
    if (!res.errorFlag) {
        showModal.value = false;
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
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Rangos de Edad</h1>
            <button @click="openModal()" class="yellow-app-button">Nuevo Rango</button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border p-3">Rango</th>
                        <th class="border p-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in ranges" :key="item.id" class="hover:bg-gray-50">
                        <td class="border p-3">{{ item.range }}</td>
                        <td class="border p-3 text-center">
                            <button @click="openModal(item)" class="text-blue-600 hover:text-blue-800 mr-4">Editar</button>
                            <button @click="remove(item.id)" class="text-red-600 hover:text-red-800">Eliminar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-xl w-96">
                <h2 class="text-xl font-semibold mb-4">{{ form.id ? 'Editar' : 'Crear' }} Rango</h2>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Edad Inicial</label>
                    <input v-model="form.init_range" type="number" class="w-full border rounded p-2">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-1">Edad Final</label>
                    <input v-model="form.finish_range" type="number" class="w-full border rounded p-2">
                </div>

                <div class="flex justify-end gap-3">
                    <button @click="showModal = false" class="px-4 py-2 text-gray-600">Cancelar</button>
                    <button @click="submit" class="yellow-app-button">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</template>

