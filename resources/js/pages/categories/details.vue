<template>
    <Head title="Categorías: detalle" />

    <MainLayout>
        <NotificationBox
            v-if="message || isError ? true : false"
            :message="message"
            :isError="isError"
            class="absolute top-0 right-0 z-10 w-100"
        />

        <div class="mx-auto min-h-100 w-170 py-10">
            <div class="text-center text-white">
                <h1 class="mb-3 text-3xl font-bold underline">
                    {{ category?.name }}
                </h1>
            </div>
            <h3 class="mt-5 text-center text-lg font-bold text-white underline">
                Preguntas registradas
            </h3>
            <div class="flex w-full items-center justify-end">
                <button @click="newQuestions()">
                    <Icon
                        class="h-9 w-9 cursor-pointer rounded-full bg-yellow-400 p-1 text-white hover:bg-yellow-300"
                        icon="ic:outline-plus"
                    />
                </button>
            </div>

            <div class="mt-5 w-full">
                <div id="table-header" class="w-full">
                    <table class="w-full table-auto text-center">
                        <thead class="bg-blue-900">
                            <tr class="border-b border-neutral-300">
                                <th class="w-20 p-2 text-white">Orden</th>
                                <th class="p-2 text-white">Nombre</th>
                                <th class="w-45 p-2 text-white">Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div id="tale-body" class="max-h-75 w-full overflow-y-scroll">
                    <table class="w-full">
                        <tbody class="bg-white">
                            <tr
                                class="border border-neutral-300 transition-all duration-90 hover:bg-blue-800 hover:text-white"
                                v-for="(question, index) in questionsByCategory"
                                :key="question.id"
                            >
                                <td class="p-2">{{ question.order }}</td>
                                <td class="p-2">{{ question.name }}</td>
                                <td class="p-2">
                                    <div
                                        class="align-center flex w-full justify-center gap-x-5"
                                    >
                                        <Icon
                                            @click="
                                                getQuestionToEdit(question.id)
                                            "
                                            class="cursor-pointer text-2xl text-yellow-600 hover:text-yellow-500"
                                            icon="ic:baseline-edit"
                                        />
                                        <Icon
                                            @click="
                                                deleteQuestion(
                                                    question.id,
                                                    index,
                                                )
                                            "
                                            class="cursor-pointer text-2xl text-red-600 hover:text-red-500"
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
                <!-- FORMULARIO CATEGORIES -->

                <h2 class="mb-6 text-center text-2xl font-bold text-gray-800">
                    {{ operation_name }} preguntas
                </h2>

                <form
                    @submit.prevent="
                        operation_name == 'Crear'
                            ? createManyQuestions()
                            : updateQuestion(questionSelectedId)
                    "
                    action=""
                    class="max-h-70 min-h-50 w-150 overflow-y-scroll"
                >
                    <div class="item-center flex justify-end space-x-3">
                        <button
                            @click="incrementFormRow"
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
                            <span>Pregunta {{ index + 1 }}</span>
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
import MainLayout from '@/layouts/main-layout.vue';
import { apiHost } from '@/store/store';

const page = usePage();

const loading = ref(false);
const message = ref('');
const isError = ref(false);

const category = ref();
const questionsByCategory = ref([]);
const operation_name = ref('create');
const isModalOpen = ref(false);

const questionSelectedId = ref(0);
const form = ref([
    {
        name: '',
        order: 0,
        category_id: parseInt(page.props.id),
    },
]);

onMounted(async () => {
    if (page.props.id) {
        category.value = await getCategory(parseInt(page.props.id));

        if (category.value.id) {
            questionsByCategory.value = await getQuestionsByCategory(
                category.value.id,
            );
        }
    }
});

const newQuestions = () => {
    isModalOpen.value = true;
    operation_name.value = 'Crear';
    form.value[0].name = '';
    form.value[0].order = 0;
};
const incrementFormRow = () => {
    form.value.push({
        name: '',
        order: 0,
        category_id: parseInt(page.props.id),
    });
};

const getCategory = async (id) => {
    try {
        const { data } = await axios.get(`${apiHost}category/show-one/${id}`);

        if (data.category) {
            return data.category;
        }

        return null;
    } catch (error) {
        console.log(error);
    }
};

const getQuestionsByCategory = async (id) => {
    try {
        const { data } = await axios.get(
            `${apiHost}question/show-by-category/${id}`,
        );

        if (data.questions) {
            return data.questions;
        }

        return null;
    } catch (error) {
        console.log(error);
    }
};

const createManyQuestions = async () => {
    try {
        loading.value = true;
        const { data, status } = await axios.post(
            `${apiHost}question/create-many`,
            form.value,
        );

        if (status == 201) {
            questionsByCategory.value = await getQuestionsByCategory(
                category.value.id,
            );
            isModalOpen.value = false;
            form.value = [
                { name: '', order: 0, category_id: parseInt(page.props.id) },
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

const deleteQuestion = async (id, index) => {
    try {
        const { status } = await axios.delete(
            `${apiHost}question/delete/${id}`,
        );

        if (status == 200) {
            questionsByCategory.value.splice(index, 1);
        }

        return null;
    } catch (error) {
        console.log(error);
    }
};

const getQuestionToEdit = async (id) => {
    try {
        const { data, status } = await axios.get(
            `${apiHost}question/show-one/${id}`,
        );

        if (status == 200) {
            form.value[0].name = data.name;
            form.value[0].order = data.order;
            isModalOpen.value = true;
            operation_name.value = 'Editar';
            questionSelectedId.value = id;
        }
    } catch (error) {
        console.log(error);
    }
};

const updateQuestion = async (id) => {
    try {
        loading.value = true;
        const { data, status } = await axios.put(
            `${apiHost}question/update/${id}`,
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
