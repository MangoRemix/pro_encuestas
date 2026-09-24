<template>
    <div
        class="mx-auto my-8 max-w-2xl rounded-2xl border border-gray-200 bg-white px-6 py-8 shadow-sm"
    >
        <h2 class="mb-8 text-xl font-bold text-gray-900">
            {{ headerLabel }}
        </h2>

        <form @submit.prevent="handleSubmit" class="space-y-6">
            <div class="flex flex-col gap-2">
                <label for="name" class="text-sm font-medium text-gray-700">
                    Nombre de la Encuesta
                </label>
                <input
                    type="text"
                    id="name"
                    v-model="form.name"
                    placeholder="Ej. Satisfacción al Cliente"
                    class="inputs-form w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 transition-all outline-none focus:border-transparent focus:ring-2 focus:ring-blue-700"
                    required
                />
            </div>

            <button
                type="submit"
                :disabled="loading"
                class="primary-button-app cursor-pointer"
            >
                {{ loading ? 'Guardando...' : submitLabel }}
            </button>
        </form>

        <NotificationBox
            v-if="message || isError"
            :message="message"
            :isError="isError"
            class="absolute top-0 right-0 z-[1100] w-100"
        />
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, reactive, computed, onMounted } from 'vue';
import { extractErrorMessage } from '@/composables/useApiError';
import { apiHost } from '@/store/store.js';
import NotificationBox from '../notification-box.vue';

const { surveyId } = defineProps({
    surveyId: { type: [Number, String], default: 0 },
});
const emit = defineEmits(['updated']);

const headerLabel = computed(() =>
    surveyId ? 'Editar Encuesta' : 'Crear Nueva Encuesta',
);

const submitLabel = computed(() =>
    surveyId ? 'Guardar Cambios' : 'Crear Encuesta',
);

// Estado del formulario
const form = reactive({
    name: '',
});

// Estados de la petición
const loading = ref(false);
const message = ref('');
const isError = ref(false);

onMounted(async () => {
    if (surveyId > 0) {
        const survey = await getSurvey(surveyId);

        if (survey) {
            form.name = survey.name;
        }
    }
});

const getSurvey = async (id) => {
    try {
        const response = await axios.get(`${apiHost}survey/show-one/${id}`);

        return response.data;
    } catch (error) {
        console.log(error);
    }
};

// Manejador del envío
const handleSubmit = async () => {
    loading.value = true;
    message.value = '';
    isError.value = false;

    try {
        let response = null;

        if (!surveyId) {
            response = await axios.post(`${apiHost}survey/create`, form);
        } else {
            response = await axios.put(
                `${apiHost}survey/update/${surveyId}`,
                form,
            );
        }

        message.value = surveyId
            ? '¡Encuesta actualizada con éxito!'
            : '¡Encuesta creada con éxito!';

        setTimeout(() => {
            message.value = '';
        }, 3000);

        if (response.status == 200) {
            emit('updated');
        } else if (response.status == 201) {
            setTimeout(() => {
                if (response.data.data.id) {
                    router.get('/surveys/create-survey/step-2', {
                        surveyId: response.data.data.id,
                    });
                }
            }, 250);
        }

        form.name = '';
    } catch (error) {
        isError.value = true;
        message.value = extractErrorMessage(error);

        setTimeout(() => {
            message.value = '';
            isError.value = false;
        }, 3000);
    } finally {
        loading.value = false;
    }
};
</script>
