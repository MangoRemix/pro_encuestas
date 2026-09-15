<script setup>
import axios from 'axios';
import { ref } from 'vue';
const emits = defineEmits(['update-categories']);
const { survey_id } = defineProps(['survey_id']);

const form = ref({
    name: '',
    order: 1,
    survey_id: parseInt(survey_id),
});

const submit = async () => {
    try {
        const response = await axios.post('/api/category/create', form.value);

        if (response.status == 201) {
            emits('update-categories', {
                success: true,
                message: 'Categoría creada con éxito',
            });
            form.value.name = '';
            form.value.order = 1;
        }
    } catch (error) {
        console.error('Error al crear categoría', error);
        emits('update-categories', {
            success: false,
            message: 'Error al crear categoría',
        });
    }
};
</script>

<template>
    <form
        @submit.prevent="submit"
        class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
    >
        <h2 class="mb-6 text-xl font-semibold text-gray-800">
            Nueva Categoría
        </h2>

        <div class="mb-4 flex items-center space-x-3">
            <div class="w-42">
                <label
                    class="block text-sm font-medium text-nowrap text-gray-700"
                    for=""
                    >Orden de categoría</label
                >
                <div class="w-18">
                    <input
                        v-model="form.order"
                        type="number"
                        class="inputs-form"
                    />
                </div>
            </div>
            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700"
                    >Nombre de la Categoría</label
                >
                <input
                    v-model="form.name"
                    type="text"
                    class="inputs-form"
                    required
                />
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="primary-button-app cursor-pointer">
                Guardar Categoría
            </button>
        </div>
        <div>
            <span class="text-sm font-bold text-red-500"
                >Nota: Prestar atención al orden de las categorías.</span
            >
        </div>
    </form>
</template>
