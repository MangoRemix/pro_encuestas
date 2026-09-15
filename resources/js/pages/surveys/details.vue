<template>
    <Head :title="`Encuesta: ${survey.name}`" />
    <MainLayout>
        <NotificationBox
            v-if="message || isError ? true : false"
            :message="message"
            :is-error="isError"
            class="absolute top-0 right-0 z-10 w-100"
        />

        <div class="w-full text-center">
            <h1 class="mx-auto mb-2 text-2xl font-bold text-white underline">
                {{ survey.name }}
            </h1>
        </div>

        <div class="mb-3 flex w-full items-center justify-between">
            <div class="space-x-2 text-xl">
                <span class="font-bold text-white">Total encuestados:</span>
                <span class="text-white">{{ survey.results_count }}</span>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <!-- CATEGORIAS -->
            <div
                class="flex h-150 flex-col rounded-xl border border-blue-700/50 bg-slate-600/50 p-4 shadow-lg backdrop-blur-md lg:col-span-3"
            >
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-lg font-extrabold text-white">
                        Categorías
                    </h3>
                    <button
                        @click="isModalOpen_categories = true"
                        class="btn-circle btn-circle-yellow h-8 w-8 cursor-pointer"
                    >
                        <Icon
                            class="text-xl text-white"
                            icon="ic:outline-plus"
                        />
                    </button>
                </div>
                <div class="custom-scrollbar flex-1 overflow-y-auto">
                    <ul class="space-y-1 text-blue-100">
                        <li
                            @click="categorySelected = category.id"
                            v-for="category in categories"
                            :key="category.id"
                            :class="`cursor-pointer rounded-lg px-3 py-2 transition-all duration-200 ${categorySelected == category.id ? 'bg-blue-600/50 font-bold text-white' : 'hover:bg-slate-700/50'}`"
                        >
                            {{ category.name }}
                        </li>
                    </ul>
                </div>
            </div>

            <!-- PREGUNTAS -->
            <div
                class="flex h-150 flex-col rounded-xl border border-blue-700/50 bg-slate-600/50 p-4 shadow-lg backdrop-blur-md lg:col-span-5"
            >
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-lg font-extrabold text-white">Preguntas</h3>
                    <button
                        @click="newQuestions()"
                        class="btn-circle btn-circle-yellow h-8 w-8 cursor-pointer disabled:opacity-50"
                        :disabled="!categorySelected"
                    >
                        <Icon
                            class="text-xl text-white"
                            icon="ic:outline-plus"
                        />
                    </button>
                </div>

                <div
                    class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-lg border border-slate-700 bg-slate-900/50"
                >
                    <div class="custom-scrollbar w-full flex-1 overflow-y-auto">
                        <table
                            class="w-full table-fixed border-collapse text-left"
                        >
                            <thead class="sticky top-0 z-10 bg-slate-900">
                                <tr
                                    class="text-xs tracking-wider text-white uppercase"
                                >
                                    <th class="w-16 p-3">Ord</th>
                                    <th class="p-3">Nombre</th>
                                    <th class="w-24 p-3 text-center">Acc</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/50">
                                <tr
                                    v-if="questions.length === 0"
                                    class="text-sm text-slate-400 italic"
                                >
                                    <td colspan="3" class="p-4 text-center">
                                        Selecciona una categoría
                                    </td>
                                </tr>
                                <tr
                                    v-for="question in questions"
                                    :key="question.id"
                                    @click="questionSelected = question.id"
                                    :class="`cursor-pointer transition-colors ${questionSelected == question.id ? 'bg-blue-600/30' : 'hover:bg-slate-600/30'}`"
                                >
                                    <td class="p-3 font-medium text-slate-200">
                                        {{ question.order }}
                                    </td>
                                    <td
                                        class="wrap-break-words p-3 whitespace-normal text-slate-200"
                                        :title="question.name"
                                    >
                                        {{ question.name }}
                                    </td>
                                    <td class="w-24 p-3">
                                        <div
                                            class="flex items-center justify-center gap-x-2"
                                        >
                                            <Link
                                                :href="`/questions/details/${question.id}`"
                                                class="text-blue-400 hover:text-blue-300"
                                            >
                                                <Icon
                                                    class="text-lg"
                                                    icon="ic:baseline-remove-red-eye"
                                                />
                                            </Link>

                                            <Icon
                                                @click="
                                                    getQuestionToEdit(
                                                        question.id,
                                                    )
                                                "
                                                class="cursor-pointer text-lg text-yellow-500 hover:text-yellow-400"
                                                icon="ic:baseline-edit"
                                            />
                                            <Icon
                                                @click.stop="
                                                    deleteQuestion(question.id)
                                                "
                                                class="cursor-pointer text-lg text-red-500 hover:text-red-400"
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

            <!-- RESPUESTAS -->
            <div
                class="flex h-150 flex-col rounded-xl border border-blue-700/50 bg-slate-600/50 p-4 shadow-lg backdrop-blur-md lg:col-span-4"
            >
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-lg font-extrabold text-white">
                        Respuestas
                    </h3>
                    <button
                        @click="newAnswers()"
                        class="btn-circle btn-circle-yellow h-8 w-8 cursor-pointer disabled:opacity-50"
                        :disabled="!questionSelected"
                    >
                        <Icon
                            class="text-xl text-white"
                            icon="ic:outline-plus"
                        />
                    </button>
                </div>

                <div
                    class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-lg border border-slate-700 bg-slate-900/50"
                >
                    <div class="custom-scrollbar w-full flex-1 overflow-y-auto">
                        <table
                            class="w-full table-fixed border-collapse text-left"
                        >
                            <thead class="sticky top-0 z-10 bg-slate-900">
                                <tr
                                    class="text-xs tracking-wider text-white uppercase"
                                >
                                    <th class="w-16 p-3">Ord</th>
                                    <th class="p-3">Nombre</th>
                                    <th class="w-24 p-3 text-center">Acc</th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-slate-700/50 text-slate-200"
                            >
                                <tr
                                    v-if="!questionSelected"
                                    class="text-sm text-slate-400 italic"
                                >
                                    <td colspan="3" class="p-4 text-center">
                                        Selecciona una pregunta para ver sus
                                        respuestas
                                    </td>
                                </tr>
                                <tr
                                    v-else-if="answersByQuestion.length === 0"
                                    class="text-sm text-slate-400 italic"
                                >
                                    <td colspan="3" class="p-4 text-center">
                                        Sin respuestas
                                    </td>
                                </tr>
                                <tr
                                    v-for="(answer, index) in answersByQuestion"
                                    :key="answer.id"
                                    class="transition-colors hover:bg-slate-600/30"
                                >
                                    <td class="p-3 font-medium">
                                        {{ answer.order }}
                                    </td>
                                    <td
                                        class="wrap-break-words p-3 whitespace-normal text-slate-200"
                                        :title="answer.name"
                                    >
                                        {{ answer.name }}
                                    </td>
                                    <td class="w-24 p-3">
                                        <div
                                            class="flex items-center justify-center gap-x-2"
                                        >
                                            <Icon
                                                @click="
                                                    getAnswerToEdit(answer.id)
                                                "
                                                class="cursor-pointer text-lg text-yellow-500 hover:text-yellow-400"
                                                icon="ic:baseline-edit"
                                            />
                                            <Icon
                                                @click="
                                                    deleteAnswer(
                                                        answer.id,
                                                        index,
                                                    )
                                                "
                                                class="cursor-pointer text-lg text-red-500 hover:text-red-400"
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
        </div>

        <!-- MODALES -->
        <Modal :show="isModalOpen" @close="isModalOpen = false">
            <div class="p-6">
                <h2 class="mb-6 text-center text-2xl font-bold text-slate-800">
                    {{ operation_name }} pregunta
                </h2>
                <form
                    @submit.prevent="
                        operation_name == 'Crear'
                            ? createManyQuestions()
                            : updateQuestion(questionSelected)
                    "
                    class="mx-auto w-full max-w-lg"
                >
                    <div class="mb-4 flex items-center justify-end space-x-3">
                        <button
                            type="button"
                            @click.prevent="incrementFormRow"
                            class="btn-circle btn-circle-yellow h-10 w-10"
                            v-if="operation_name != 'Editar'"
                        >
                            <Icon
                                class="text-2xl text-white"
                                icon="ic:outline-plus"
                            />
                        </button>
                        <button
                            type="submit"
                            class="btn-circle btn-circle-blue h-10 w-10"
                        >
                            <Icon
                                class="text-2xl text-white"
                                icon="ic:round-save"
                            />
                        </button>
                    </div>
                    <div
                        class="custom-scrollbar max-h-[60vh] overflow-y-auto pr-2"
                    >
                        <div
                            v-for="(formRow, index) in formQuestion"
                            :key="index"
                            class="relative mb-4 rounded-lg border border-slate-200 bg-slate-50 p-4"
                        >
                            <button
                                type="button"
                                v-if="index > 0"
                                @click="formQuestion.splice(index, 1)"
                                class="absolute top-2 right-2 text-red-500 hover:text-red-700"
                            >
                                <Icon
                                    icon="ic:baseline-close"
                                    class="text-xl"
                                />
                            </button>
                            <div class="mb-2 text-sm font-bold text-slate-700">
                                Pregunta {{ index + 1 }}
                            </div>
                            <div class="grid grid-cols-4 gap-2">
                                <div class="col-span-1">
                                    <label
                                        class="mb-1 block text-xs font-bold text-slate-600"
                                        >Orden</label
                                    >
                                    <input
                                        required
                                        v-model="formRow.order"
                                        min="1"
                                        type="number"
                                        class="inputs-form"
                                    />
                                </div>
                                <div class="col-span-3">
                                    <label
                                        class="mb-1 block text-xs font-bold text-slate-600"
                                        >Nombre</label
                                    >
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
            </div>
        </Modal>

        <Modal :show="isModalOpen_answers" @close="isModalOpen_answers = false">
            <div class="p-6">
                <h2 class="mb-6 text-center text-2xl font-bold text-slate-800">
                    {{ operation_name }} respuesta
                </h2>
                <form
                    @submit.prevent="
                        operation_name == 'Crear'
                            ? createManyAnswers()
                            : updateAnswer(answerSelectedId)
                    "
                    class="mx-auto w-full max-w-lg"
                >
                    <div class="mb-4 flex items-center justify-end space-x-3">
                        <button
                            type="button"
                            @click.prevent="incrementFormRow_answer"
                            class="btn-circle btn-circle-yellow h-10 w-10"
                            v-if="operation_name != 'Editar'"
                        >
                            <Icon
                                class="text-2xl text-white"
                                icon="ic:outline-plus"
                            />
                        </button>
                        <button
                            type="submit"
                            class="btn-circle btn-circle-blue h-10 w-10"
                        >
                            <Icon
                                class="text-2xl text-white"
                                icon="ic:round-save"
                            />
                        </button>
                    </div>
                    <div
                        class="custom-scrollbar max-h-[60vh] overflow-y-auto pr-2"
                    >
                        <div
                            v-for="(formRow, index) in formAnswer"
                            :key="index"
                            class="relative mb-4 rounded-lg border border-slate-200 bg-slate-50 p-4"
                        >
                            <button
                                type="button"
                                v-if="index > 0"
                                @click="formAnswer.splice(index, 1)"
                                class="absolute top-2 right-2 text-red-500 hover:text-red-700"
                            >
                                <Icon
                                    icon="ic:baseline-close"
                                    class="text-xl"
                                />
                            </button>
                            <div class="mb-2 text-sm font-bold text-slate-700">
                                Respuesta {{ index + 1 }}
                            </div>
                            <div class="grid grid-cols-4 gap-2">
                                <div class="col-span-1">
                                    <label
                                        class="mb-1 block text-xs font-bold text-slate-600"
                                        >Orden</label
                                    >
                                    <input
                                        required
                                        v-model="formRow.order"
                                        min="1"
                                        type="number"
                                        class="inputs-form"
                                    />
                                </div>
                                <div class="col-span-3">
                                    <label
                                        class="mb-1 block text-xs font-bold text-slate-600"
                                        >Nombre</label
                                    >
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
            </div>
        </Modal>

        <!-- MODAL PARA CREAR CATEGORIES -->
        <Modal
            :show="isModalOpen_categories"
            @close="isModalOpen_categories = false"
        >
            <div class="mx-auto max-w-2xl min-w-150">
                <CategoryForm
                    :survey_id="page.props.id"
                    @update-categories="updateCategories"
                />
            </div>
        </Modal>
    </MainLayout>
</template>
<script setup>
import { Icon } from '@iconify/vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref, watch } from 'vue';
import CategoryForm from '@/components/forms/category-form.vue';
import Modal from '@/components/modal.vue';
import NotificationBox from '@/components/notification-box.vue';

import { useAnswers } from '@/composables/api/answers';
import {
    createMany,
    getQuestion,
    getQuestionsByCategory,
} from '@/composables/api/questions';
import { getCategoriesBySurvey, getSurvey } from '@/composables/api/surveys';
import { useNotification } from '@/composables/useNotification';
import MainLayout from '@/layouts/main-layout.vue';
import { apiHost } from '@/store/store';

const { message, isError, notify } = useNotification();
const {
    getAnswersByQuestion: getAnswersByQuestionApi,
    deleteAnswer: deleteAnswerApi,
    createManyAnswers: createManyAnswersApi,
    updateAnswer: updateAnswerApi,
} = useAnswers();

const operation_name = ref('create');
const isModalOpen = ref(false);
const isModalOpen_answers = ref(false);
const isModalOpen_categories = ref(false);
const questions = ref([]);
const categories = ref([]);
const survey = ref([]);
const page = usePage();
const categorySelected = ref(0);
const surveySelected = ref(0);
const questionSelected = ref(0);
const answerSelectedId = ref(0);
const answersByQuestion = ref([]);

const formQuestion = ref([
    {
        name: '',
        order: 0,
        category_id: parseInt(page.props.categoryId),
    },
]);

const formAnswer = ref([
    {
        name: '',
        order: 0,
        question_id: 0,
    },
]);

// Function to delete a question
const deleteQuestion = async (id) => {
    // Placeholder: You'll need to implement the actual API call here.
    // Example: You might have a deleteQuestionApi function in your composables.
    console.log(`Deleting question with id: ${id}`);
    // const success = await deleteQuestionApi(id); // Uncomment and adapt this line
    // if (success) {
    //     // Refresh questions list after deletion
    //     const { data: questions_ } = await getQuestionsByCategory(categorySelected.value);
    //     questions.value = questions_;
    //     notify("Pregunta eliminada correctamente");
    // } else {
    //     notify("Error al eliminar la pregunta", true);
    // }
};

onMounted(async () => {
    const { data } = await getSurvey(page.props.id);

    setTimeout(() => {
        if (page.props.categoryId) {
            surveySelected.value = page.props.id;
            categorySelected.value = parseInt(page.props.categoryId);
        } else if (page.props.id) {
            surveySelected.value = page.props.id;
        }
    }, 750);

    if (data) {
        survey.value = data;
    }
});

watch(surveySelected, async () => {
    router.get(
        `/surveys/details/${page.props.id}`,
        {},
        {
            preserveState: true, // Evita que Vue destruya el estado del componente
            replace: true, // No satura el historial del botón "Atrás" del navegador
        },
    );

    await updateCategories();
});
watch(categorySelected, async (value) => {
    router.get(
        `/surveys/details/${page.props.id}`,
        {
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
        answersByQuestion.value = [];
    } else if (errorFlag) {
        notify(responseMessage, true);
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
    const { data, errorFlag, responseMessage } = await getQuestion(id);

    if (data) {
        formQuestion.value[0].name = data.name;
        formQuestion.value[0].order = data.order;
        isModalOpen.value = true;
        operation_name.value = 'Editar';
        questionSelected.value = id;
    } else if (errorFlag) {
        notify(responseMessage, true);
    }
};

const createManyQuestions = async () => {
    const { data, errorFlag, responseMessage } = await createMany(
        formQuestion.value,
    );
    console.log(data);

    if (data) {
        const { data: questions_ } = await getQuestionsByCategory(
            page.props.categoryId,
        );
        questions.value = questions_;
        notify(data);
        formQuestion.value = [
            {
                name: '',
                order: 0,
                category_id: parseInt(page.props.categoryId),
            },
        ];
    } else if (errorFlag) {
        notify(responseMessage, true);
    }
};
const newQuestions = () => {
    isModalOpen.value = true;
    operation_name.value = 'Crear';
    formQuestion.value[0].name = '';
    formQuestion.value[0].order = 0;
};

watch(questionSelected, async (value) => {
    router.get(
        `/surveys/details/${page.props.id}`,
        {
            categoryId: page.props.categoryId,
            questionId: value,
            //page: page.value,
        },
        {
            preserveState: true, // Evita que Vue destruya el estado del componente
            replace: true, // No satura el historial del botón "Atrás" del navegador
        },
    );

    if (value) {
        const { data } = await getAnswersByQuestionApi(value);
        answersByQuestion.value = data;
    }
});

//ANSWERS METHODS

// const getAnswersByQuestion = async (id) => {

//     try {
//         const {data,error} = await axios.get(`${apiHost}answer/show-by-question/${id}`)

//         if(data.answers)
//             return data.answers
//         return null
//     } catch (error) {
//         console.log(error)
//     }
// }
const createManyAnswers = async () => {
    const { success } = await createManyAnswersApi(formAnswer.value);

    if (success) {
        answersByQuestion.value = await getAnswersByQuestionApi(
            questionSelected.value,
        );
        formAnswer.value = [
            {
                name: '',
                order: 0,
                question_id: questionSelected.value,
            },
        ];
        notify('Respuestas creadas correctamente');
        isModalOpen_answers.value = false;
    } else {
        notify('Error al crear respuestas', true);
    }
};

const deleteAnswer = async (id, index) => {
    const success = await deleteAnswerApi(id);

    if (success) {
        answersByQuestion.value.splice(index, 1);
        notify('Respuesta eliminada');
    } else {
        notify('Error al eliminar', true);
    }
};

const getAnswerToEdit = async (id) => {
    try {
        const { data, status } = await axios.get(
            `${apiHost}answer/show-one/${id}`,
        );

        if (status == 200) {
            formAnswer.value[0].name = data.answer.name;
            formAnswer.value[0].order = data.answer.order;
            formAnswer.value[0].question_id = data.answer.question_id;
            isModalOpen_answers.value = true;
            operation_name.value = 'Editar';
            answerSelectedId.value = id;
        }
    } catch (error) {
        console.log(error);
    }
};
const updateAnswer = async (id) => {
    const { success } = await updateAnswerApi(id, formAnswer.value[0]);

    if (success) {
        notify('Respuesta actualizada');

        answersByQuestion.value = await getAnswersByQuestionApi(
            questionSelected.value,
        );
        isModalOpen_answers.value = false;
    } else {
        notify('Error al actualizar', true);
    }
};
const incrementFormRow_answer = () => {
    formAnswer.value.push({
        name: '',
        order: 0,
        question_id: questionSelected.value,
    });
};

// CATEGORIES METHODS
const updateCategories = async () => {
    const { data, errorFlag, responseMessage } = await getCategoriesBySurvey(
        page.props.id,
    );

    if (data) {
        categories.value = data;
    } else if (errorFlag) {
        notify(responseMessage, true);
    }
};
</script>

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
