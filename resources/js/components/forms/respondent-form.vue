<template>
    <div
        v-if="page.props.id"
        class="mx-auto my-8 max-w-md rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
    >
        <h2 class="mb-6 text-center text-2xl font-bold text-gray-800">
            Registrar Participante
        </h2>

        <form @submit.prevent="handleSubmit" class="space-y-5">
            <!-- Sexo -->
            <div class="flex flex-col gap-1.5">
                <label for="sex_id" class="text-sm font-semibold text-gray-700"
                    >Sexo:</label
                >
                <select
                    id="sex_id"
                    v-model="form.sex_id"
                    class="inputs-form"
                    required
                >
                    <option v-for="sex in sexes" :key="sex.id" :value="sex.id">
                        {{ sex.abbreviation }}
                    </option>
                </select>
            </div>

            <!-- Edad -->
            <div class="flex flex-col gap-1.5">
                <label for="age" class="text-sm font-semibold text-gray-700"
                    >Edad:</label
                >
                <input
                    type="number"
                    id="age"
                    v-model="form.age"
                    class="inputs-form"
                    required
                    min="0"
                    max="120"
                />
            </div>

            <!-- Parroquia -->
            <div class="flex flex-col gap-1.5">
                <label
                    for="parish_id"
                    class="text-sm font-semibold text-gray-700"
                    >Parroquia:</label
                >
                <select
                    id="parish_id"
                    v-model="form.parish_id"
                    class="inputs-form"
                    required
                >
                    <option value="" disabled>Seleccione...</option>
                    <option
                        v-for="parish in parishes"
                        :key="parish.id"
                        :value="parish.id"
                    >
                        {{ parish.name }}
                    </option>
                </select>
            </div>

            <!-- Botón de Envío -->
            <button
                type="submit"
                :disabled="loading"
                class="primary-button-app w-full cursor-pointer"
            >
                {{ loading ? 'Guardando...' : 'Registrar Participante' }}
            </button>
        </form>

        <!-- Mensajes de Estado -->
        <NotificationBox
            v-if="message"
            :message="message"
            :isError="isError"
            class="absolute top-0 right-0 z-10 w-100"
        />
    </div>
</template>

<script setup>
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, reactive, onMounted } from 'vue';
import { apiHost } from '@/store/store.js';
import NotificationBox from '../notification-box.vue';

onMounted(async () => {
    sexes.value = await getSexList();
    parishes.value = await getParishList();
    console.log(page.props);
});

const page = usePage();

// PERSON RESOURCES
const updatePerson = async (data) => {
    data['rol_id'] = 2;

    return await axios.patch(
        `${apiHost}person/respondent/update/${parseInt(page.props.id)}`,
        data,
    );
};

const form = reactive({
    name: `Encuestado ${page.props.id}`,
    sex_id: '',
    age: '',
    parish_id: '',
});

const sexes = ref([]);
const parishes = ref([]);
const loading = ref(false);
const message = ref('');
const isError = ref(false);

const handleSubmit = async () => {
    loading.value = true;
    message.value = '';
    isError.value = false;

    try {
        const { status } = await updatePerson(form);

        if (status == 200) {
            setTimeout(() => {
                router.get(
                    `/poll-users/step-3/${page.props.id}/survey/${page.props.surveyId}`,
                );
            }, 2000);
        }

        message.value = 'Participante registrado con éxito.';

        // Limpiar el formulario
        form.sex_id = '';
        form.age = '';
        form.parish_id = '';
    } catch (error) {
        isError.value = true;
        message.value =
            error.response?.data?.message || 'Ocurrió un error al registrar.';
        setTimeout(() => {
            message.value = '';
            isError.value = false;
        }, 3000);
        console.error(
            'Error al crear persona:',
            error.response?.data?.errors || error,
        );
    } finally {
        loading.value = false;
    }
};
const getSexList = async () => {
    try {
        const sex = await axios.get(`${apiHost}sex/show-all`);

        return sex.data;
    } catch (error) {
        console.log(error);
    }
};

const getParishList = async () => {
    try {
        const response = await axios.get(`${apiHost}parish/show-all`);

        return response.data;
    } catch (error) {
        console.error(error);
    }
};
</script>
