<template>
    <Head :title="'Categorías'" />
    <MainLayout>
        <NotificationBox
            v-if="message || isError ? true : false"
            :message="message"
            :is-error="isError"
            class="absolute top-0 right-0 z-10 w-100"
        />
        <div class="mx-auto my-3 flex min-h-10 w-100 flex-col">
            <select
                name=""
                v-model="surveySelected"
                id=""
                class="inputs-form bg-white"
            >
                <option :value="0">Seleccione encuesta</option>
                <option
                    :value="survey.id"
                    :key="survey.id"
                    class="p-2 text-neutral-800"
                    v-for="survey in surveys"
                >
                    {{ survey.name }}
                </option>
            </select>
        </div>
        <div class="mb-3 flex w-full items-center justify-end">
            <button
                @click="newQuestions()"
                class="flex cursor-pointer items-center gap-x-3 rounded-2xl bg-yellow-400 px-2 font-bold text-white hover:bg-yellow-300"
            >
                Crear preguntas
                <Icon class="h-9 w-9 p-1" icon="ic:outline-plus" />
            </button>
        </div>
        <div class="flex space-x-2">
            <div
                class="h-125 w-full scrollbar-thumb-blue-800 scrollbar-track-white/30 overflow-y-scroll rounded-xl border border-blue-700/50 bg-white/30 p-6 shadow-lg backdrop-blur-md sm:w-[75%] md:w-[55%] lg:w-[35%]"
            >
                <ul class="mt-2 text-blue-100">
                    <li
                        @click="categorySelected = category.id"
                        v-for="category in categories"
                        :key="category.id"
                        :class="`cursor-pointer py-1 transition-all duration-75 hover:font-bold hover:text-yellow-400 hover:underline ${categorySelected == category.id ? 'text-yellow-400' : ''} `"
                    >
                        {{ category.name }}
                    </li>
                </ul>
            </div>

            <div
                class="h-125 w-full rounded-xl border border-blue-700/50 bg-white/30 p-6 shadow-lg backdrop-blur-md"
            >
                <h3 class="mb-3 text-center text-xl font-extrabold text-white">
                    Listado de preguntas
                </h3>
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
                    class="max-h-90 w-full scrollbar-thumb-blue-800 scrollbar-track-white/30 overflow-y-scroll"
                >
                    <table class="w-full table-fixed">
                        <tbody class="">
                            <tr
                                :id="`question-${index}`"
                                v-for="(question, index) in questions"
                                :key="question.id"
                                class="border-b border-neutral-400 text-white"
                            >
                                <td class="w-30 py-2">{{ question.order }}</td>
                                <td class="py-2">
                                    <Link
                                        :href="`/questions/details/${question.id}`"
                                    >
                                        {{ question.name }}
                                    </Link>
                                </td>
                                <td class="w-45 py-2">
                                    <div
                                        class="flex w-full items-center justify-center gap-x-3"
                                    >
                                        <Link
                                            :href="`/questions/details/${question.id}`"
                                        >
                                            <Icon
                                                class="cursor-pointer text-lg text-blue-600 hover:text-blue-500 md:text-2xl"
                                                icon="ic:baseline-remove-red-eye"
                                            />
                                        </Link>

                                        <Icon
                                            @click="
                                                getQuestionToEdit(question.id)
                                            "
                                            class="cursor-pointer text-2xl text-yellow-600 hover:text-yellow-500"
                                            icon="ic:baseline-edit"
                                        />
                                        <Icon
                                            class="cursor-pointer text-lg text-red-600 hover:text-red-500 md:text-2xl"
                                            icon="ic:baseline-restore-from-trash"
                                        />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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
                        : updateQuestion(questionSelected)
                "
                action=""
                class="h-70 w-150"
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
                <div class="h-full max-h-full w-full overflow-y-scroll">
                    <div
                        v-for="(formRow, index) in formQuestion"
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
                </div>
            </form>
        </Modal>
    </MainLayout>
</template>
<script setup>
import { Icon } from '@iconify/vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';
import Modal from '@/components/modal.vue';
import NotificationBox from '@/components/notification-box.vue';
import {
    createMany,
    getQuestion,
    getQuestionsByCategory,
} from '@/composables/api/questions';
import { getCategoriesBySurvey, getSurveys } from '@/composables/api/surveys';
import MainLayout from '@/layouts/main-layout.vue';

const operation_name = ref('create');
const isModalOpen = ref(false);

const questions = ref([]);
const categories = ref([]);
const surveys = ref([]);
const page = usePage();
const categorySelected = ref(0);
const surveySelected = ref(0);
const questionSelected = ref(0);

const formQuestion = ref([
    {
        name: '',
        order: 0,
        category_id: parseInt(page.props.categoryId),
    },
]);

const message = ref();
const isError = ref(false);

onMounted(async () => {
    const { data } = await getSurveys({});

    setTimeout(() => {
        if (page.props.categoryId) {
            surveySelected.value = page.props.surveyId;
            categorySelected.value = parseInt(page.props.categoryId);
        } else {
            if (page.props.surveyId) {
                surveySelected.value = page.props.surveyId;
            }
        }
    }, 750);

    if (data) {
        surveys.value = data;
    }
});

watch(surveySelected, async (value) => {
    router.get(
        '/categories',
        {
            surveyId: value,
            //page: page.value,
        },
        {
            preserveState: true, // Evita que Vue destruya el estado del componente
            replace: true, // No satura el historial del botón "Atrás" del navegador
        },
    );

    const { data, errorFlag, responseMessage } =
        await getCategoriesBySurvey(value);

    if (data) {
        categories.value = data;
    } else {
        if (errorFlag) {
            isError.value = true;
            message.value = responseMessage;
            setTimeout(() => {
                message.value = '';
            }, 3500);
        }
    }
});

watch(categorySelected, async (value) => {
    router.get(
        '/categories',
        {
            surveyId: surveySelected.value,
            categoryId: value,
            //page: page.value,
        },
        {
            preserveState: true, // Evita que Vue destruya el estado del componente
            replace: true, // No satura el historial del botón "Atrás" del navegador
        },
    );

    const { data, errorFlag, responseMessage } =
        await getQuestionsByCategory(value);

    if (data) {
        questions.value = data;
    } else {
        if (errorFlag) {
            isError.value = true;
            message.value = responseMessage;
            setTimeout(() => {
                message.value = '';
            }, 3500);
        }
    }
});

const incrementFormRow = () => {
    formQuestion.value.push({
        name: '',
        order: 0,
        category_id: parseInt(page.props.categoryId),
    });
};

const getQuestionToEdit = async (id) => {
    try {
        const { data, errorFlag, responseMessage } = await getQuestion(id);
        console.log('question: ', data);

        if (data) {
            formQuestion.value[0].name = data.name;
            formQuestion.value[0].order = data.order;
            isModalOpen.value = true;
            operation_name.value = 'Editar';
            questionSelected.value = id;
        }

        if (errorFlag) {
            isError.value = true;
            message.value = responseMessage;
            setTimeout(() => {
                message.value = '';
            }, 3500);
        }
    } catch (error) {
        console.log(error);
    }
};

const createManyQuestions = async () => {
    try {
        const { data, errorFlag, responseMessage } = await createMany(
            formQuestion.value,
        );

        if (data) {
            const refreshed = await getQuestionsByCategory(
                categorySelected.value,
            );

            if (refreshed.data) {
                questions.value = refreshed.data;
            }

            message.value = data;
            isModalOpen.value = false;
            formQuestion.value = [
                {
                    name: '',
                    order: 0,
                    category_id: parseInt(page.props.categoryId),
                },
            ];
        }

        if (errorFlag) {
            isError.value = true;
            message.value = responseMessage;
            setTimeout(() => {
                message.value = '';
            }, 3500);
        }
    } catch (error) {
        console.log(error);
    }
};

const newQuestions = () => {
    isModalOpen.value = true;
    operation_name.value = 'Crear';
    formQuestion.value[0].name = '';
    formQuestion.value[0].order = 0;
};
</script>
