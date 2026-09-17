<template>
    <Head title="Preguntas: detalle" />

    <MainLayout>
        <NotificationBox
            v-if="message || isError ? true : false"
            :message="message"
            :isError="isError"
            class="absolute top-0 right-0 z-10 w-100"
        />
        <div class="mx-auto min-h-100 w-270 py-10">
            <div class="text-center text-white">
                <h1 class="mb-3 text-3xl font-bold underline">
                    {{ question?.name }}
                </h1>
            </div>
            <div
                class="w-full rounded-xl border border-blue-700/50 bg-white/30 p-6 shadow-lg backdrop-blur-md"
            >
                <h3 class="mb-3 text-center text-xl font-extrabold text-white">
                    Listado de respuestas
                </h3>
                <div class="mb-2 flex w-full items-center justify-end">
                    <button @click="newAnswers()">
                        <Icon
                            class="h-9 w-9 cursor-pointer rounded-full bg-yellow-400 p-1 text-white hover:bg-yellow-300"
                            icon="ic:outline-plus"
                        />
                    </button>
                </div>

                <div id="table-header" class="h-10 w-full">
                    <table class="w-full table-fixed text-left">
                        <thead>
                            <tr
                                class="border-b border-white/30 text-lg text-white"
                            >
                                <th class="w-30">Orden</th>
                                <th>Nombre</th>
                                <th class="w-55 text-center">Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div
                    id="table-body"
                    class="max-h-75 w-full scrollbar-thumb-blue-800 scrollbar-track-white/30 overflow-y-scroll"
                >
                    <table class="w-full table-fixed">
                        <tbody class="text-white">
                            <tr
                                class="border-b border-neutral-400"
                                v-for="(answer, index) in answersByQuestion"
                                :key="answer.id"
                            >
                                <td class="w-30 py-2">{{ answer.order }}</td>
                                <td class="py-2">{{ answer.name }}</td>
                                <td class="w-55 py-2">
                                    <div
                                        class="flex w-full items-center justify-center gap-x-3"
                                    >
                                        <Icon
                                            @click="getAnswerToEdit(answer.id)"
                                            class="cursor-pointer text-2xl text-yellow-600 hover:text-yellow-500"
                                            icon="ic:baseline-edit"
                                        />
                                        <Icon
                                            @click="
                                                deleteAnswer(answer.id, index)
                                            "
                                            class="cursor-pointer text-2xl text-red-600 hover:text-red-500"
                                            icon="ic:baseline-restore-from-trash"
                                            title="Ocultar"
                                        />
                                        <Icon
                                            v-if="isAdmin"
                                            @click="
                                                forceDeleteAnswerRow(
                                                    answer.id,
                                                    index,
                                                )
                                            "
                                            class="cursor-pointer text-2xl text-red-800 hover:text-red-600"
                                            icon="ic:baseline-delete-forever"
                                            title="Eliminar permanentemente"
                                        />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <Modal :show="isModalOpen" @close="isModalOpen = false">
                <!-- FORMULARIO RESPUESTAS -->

                <h2 class="mb-6 text-center text-2xl font-bold text-gray-800">
                    {{ operation_name }} respuestas
                </h2>

                <form
                    @submit.prevent="
                        operation_name == 'Crear'
                            ? createManyAnswers()
                            : updateAnswer(answerSelectedId)
                    "
                    action=""
                    class="max-h-70 min-h-50 w-150 overflow-y-scroll"
                >
                    <div class="item-center flex justify-end space-x-3">
                        <button
                            @click.prevent="incrementFormRow"
                            class=""
                            v-if="operation_name != 'Editar'"
                        >
                            <Icon
                                class="h-8 w-8 cursor-pointer rounded-full bg-yellow-400 p-1 text-white hover:bg-yellow-300"
                                icon="ic:outline-plus"
                            />
                        </button>

                        <button type="submit" class="cursor-pointer">
                            <Icon
                                class="h-8 w-8 rounded-full bg-blue-600 p-1 text-xs text-white hover:bg-blue-700"
                                icon="ic:round-save"
                            />
                        </button>
                    </div>
                    <div
                        v-for="(formRow, index) in form"
                        :key="index"
                        class="mb-3"
                    >
                        <div class="mb-3 text-center font-bold">
                            <span>Respuesta {{ index + 1 }}</span>
                        </div>
                        <div
                            class="flex items-center justify-between space-x-2"
                        >
                            <div class="flex w-35 items-center space-x-2">
                                <label for="" class="text-sm font-bold"
                                    >Orden:
                                </label>
                                <input
                                    required
                                    v-model="formRow.order"
                                    min="1"
                                    type="number"
                                    class="inputs-form"
                                />
                            </div>

                            <div class="flex w-full items-center space-x-2">
                                <label for="" class="text-sm font-bold"
                                    >Nombre:
                                </label>
                                <input
                                    required
                                    minlength="5"
                                    v-model="formRow.name"
                                    type="text"
                                    class="inputs-form"
                                />
                            </div>
                        </div>
                    </div>
                </form>
            </Modal>
        </div>
    </MainLayout>
</template>
<script setup>
import { Icon } from '@iconify/vue';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref } from 'vue';
import Modal from '@/components/modal.vue';
import NotificationBox from '@/components/notification-box.vue';
import { hideAnswer, forceDeleteAnswer } from '@/composables/api/answers';
import { useAuth } from '@/composables/useAuth';
import MainLayout from '@/layouts/main-layout.vue';
import { apiHost } from '@/store/store';

const page = usePage();
const { isAdmin } = useAuth();

const loading = ref(false);
const message = ref('');
const isError = ref(false);

const question = ref();
const answersByQuestion = ref([]);
const operation_name = ref('create');
const isModalOpen = ref(false);

const answerSelectedId = ref(0);
const form = ref([
    {
        name: '',
        order: 0,
        question_id: parseInt(page.props.id),
    },
]);

onMounted(async () => {
    question.value = await getQuestion(parseInt(page.props.id));

    if (question.value.id) {
        answersByQuestion.value = await getAnswersByQuestion(question.value.id);
    }
});

const newAnswers = () => {
    isModalOpen.value = true;
    operation_name.value = 'Crear';
    form.value[0].name = '';
    form.value[0].order = 0;
};
const incrementFormRow = () => {
    form.value.push({
        name: '',
        order: 0,
        question_id: parseInt(page.props.id),
    });
};

const getQuestion = async (id) => {
    try {
        const { data } = await axios.get(`${apiHost}question/show-one/${id}`);

        if (data.question) {
            return data.question;
        }

        return null;
    } catch (error) {
        console.log(error);
    }
};

const getAnswersByQuestion = async (id) => {
    try {
        const { data } = await axios.get(
            `${apiHost}answer/show-by-question/${id}`,
        );

        if (data.answers) {
            return data.answers;
        }

        return null;
    } catch (error) {
        console.log(error);
    }
};

const createManyAnswers = async () => {
    try {
        loading.value = true;
        const { data, status } = await axios.post(
            `${apiHost}answer/create-many`,
            form.value,
        );

        if (status == 201) {
            answersByQuestion.value = await getAnswersByQuestion(
                question.value.id,
            );
            isModalOpen.value = false;
            form.value = [
                {
                    name: '',
                    order: 0,
                    question_id: parseInt(page.props.id),
                },
            ];
            message.value = data.message;
        }

        return null;
    } catch (error) {
        console.log(error);
        isError.value = true;
        message.value = error.response.data;
    } finally {
        loading.value = false;
        setTimeout(() => {
            message.value = '';
        }, 3500);
    }
};

const deleteAnswer = async (id, index) => {
    loading.value = true;

    const { success, message: apiMessage } = await hideAnswer(id);

    if (success) {
        answersByQuestion.value.splice(index, 1);
        message.value = 'Respuesta ocultada correctamente';
    } else {
        isError.value = true;
        message.value = apiMessage;
    }

    setTimeout(() => {
        loading.value = false;
        message.value = '';
    }, 3500);
};

const forceDeleteAnswerRow = async (id, index) => {
    if (!confirm('¿Eliminar esta respuesta de forma permanente?')) {
        return;
    }

    loading.value = true;

    const { success, message: apiMessage } = await forceDeleteAnswer(id);

    if (success) {
        answersByQuestion.value.splice(index, 1);
        message.value = 'Respuesta eliminada permanentemente';
    } else {
        isError.value = true;
        message.value = apiMessage;
    }

    setTimeout(() => {
        loading.value = false;
        message.value = '';
    }, 3500);
};

const getAnswerToEdit = async (id) => {
    try {
        const { data, status } = await axios.get(
            `${apiHost}answer/show-one/${id}`,
        );

        if (status == 200) {
            form.value[0].name = data.name;
            form.value[0].order = data.order;
            isModalOpen.value = true;
            operation_name.value = 'Editar';
            answerSelectedId.value = id;
        }
    } catch (error) {
        console.log(error);
    }
};

const updateAnswer = async (id) => {
    try {
        loading.value = true;
        const { data, status } = await axios.put(
            `${apiHost}answer/update/${id}`,
            form.value[0],
        );

        if (status == 200) {
            message.value = data.message;
        }
    } catch (error) {
        isError.value = true;
        const { response } = error;
        message.value = response?.data;
    } finally {
        loading.value = false;
        setTimeout(() => {
            message.value = '';
        }, 3500);
    }
};
</script>
