<template>
    <Head :title="`Encuesta: ${survey.name}`" />
    <MainLayout>
        <NotificationBox
            v-if="message || isError ? true : false"
            :message="message"
            :is-error="isError"
            class="absolute top-0 right-0 z-[1100] w-100"
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

        <div
            v-if="structureLocked"
            class="mb-3 rounded-lg border border-yellow-500/50 bg-yellow-500/10 px-4 py-2 text-sm text-yellow-200"
        >
            Esta encuesta ya tiene datos recolectados; su estructura
            (categorías, preguntas y respuestas) no puede modificarse.
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <!-- CATEGORIAS -->
            <div
                class="flex h-150 flex-col rounded-xl border border-blue-700/50 bg-slate-600/50 p-4 shadow-lg backdrop-blur-md lg:col-span-3"
            >
                <div class="mb-3 flex items-center justify-between gap-2">
                    <h3 class="text-lg font-extrabold text-white">
                        Categorías
                    </h3>
                    <div class="flex items-center gap-2">
                        <button
                            v-if="isAdmin"
                            @click="
                                showHiddenCategories = !showHiddenCategories;
                                refreshCategories();
                            "
                            class="cursor-pointer rounded-full border border-slate-500 px-2 py-1 text-xs text-slate-200 transition-colors hover:bg-slate-700/50"
                            :class="{ 'bg-slate-700/70': showHiddenCategories }"
                        >
                            {{
                                showHiddenCategories
                                    ? 'Ocultar vistas'
                                    : 'Ver ocultas'
                            }}
                        </button>
                        <button
                            @click="
                                editingCategoryId = 0;
                                isModalOpen_categories = true;
                            "
                            class="btn-circle btn-circle-yellow h-8 w-8 cursor-pointer disabled:opacity-50"
                            :disabled="structureLocked"
                        >
                            <Icon
                                class="text-xl text-white"
                                icon="ic:outline-plus"
                            />
                        </button>
                    </div>
                </div>
                <div class="custom-scrollbar flex-1 overflow-y-auto">
                    <draggable
                        v-model="categories"
                        item-key="id"
                        tag="ul"
                        class="space-y-1 text-blue-100"
                        :disabled="structureLocked"
                        @end="onReorderCategories"
                    >
                        <template #item="{ element: category }">
                            <li
                                :class="`flex items-center justify-between gap-1 rounded-lg px-3 py-2 transition-all duration-200 ${categorySelected == category.id ? 'bg-blue-600/50 font-bold text-white' : 'hover:bg-slate-700/50'} ${category.deleted_at ? 'italic opacity-50' : ''}`"
                            >
                                <span
                                    @click="categorySelected = category.id"
                                    class="min-w-0 flex-1 cursor-pointer truncate"
                                    >{{ category.name }}</span
                                >
                                <div class="flex shrink-0 items-center gap-1.5">
                                    <template v-if="category.deleted_at">
                                        <Icon
                                            @click.stop="
                                                restoreCategoryRow(category.id)
                                            "
                                            class="cursor-pointer text-base text-green-400 hover:text-green-300"
                                            icon="ic:baseline-restore"
                                            title="Restaurar"
                                        />
                                    </template>
                                    <template v-else>
                                        <Icon
                                            v-if="!structureLocked"
                                            @click.stop="
                                                editCategory(category.id)
                                            "
                                            class="cursor-pointer text-base text-yellow-500 hover:text-yellow-400"
                                            icon="ic:baseline-edit"
                                            title="Editar"
                                        />
                                        <Icon
                                            v-if="!structureLocked"
                                            @click.stop="
                                                hideCategoryRow(category.id)
                                            "
                                            class="cursor-pointer text-base text-red-500 hover:text-red-400"
                                            icon="ic:round-visibility-off"
                                            title="Ocultar"
                                        />
                                    </template>
                                    <Icon
                                        v-if="isAdmin"
                                        @click.stop="
                                            forceDeleteCategoryRow(category.id)
                                        "
                                        class="cursor-pointer text-base text-red-800 hover:text-red-600"
                                        icon="ic:baseline-delete-forever"
                                        title="Eliminar permanentemente"
                                    />
                                </div>
                            </li>
                        </template>
                    </draggable>
                </div>
            </div>

            <!-- PREGUNTAS -->
            <div
                class="flex h-150 flex-col rounded-xl border border-blue-700/50 bg-slate-600/50 p-4 shadow-lg backdrop-blur-md lg:col-span-5"
            >
                <div class="mb-3 flex items-center justify-between gap-2">
                    <h3 class="text-lg font-extrabold text-white">Preguntas</h3>
                    <div class="flex items-center gap-2">
                        <button
                            v-if="isAdmin"
                            @click="
                                showHiddenQuestions = !showHiddenQuestions;
                                refreshQuestions();
                            "
                            class="cursor-pointer rounded-full border border-slate-500 px-2 py-1 text-xs text-slate-200 transition-colors hover:bg-slate-700/50"
                            :class="{ 'bg-slate-700/70': showHiddenQuestions }"
                        >
                            {{
                                showHiddenQuestions
                                    ? 'Ocultar vistas'
                                    : 'Ver ocultas'
                            }}
                        </button>
                        <button
                            @click="newQuestions()"
                            class="btn-circle btn-circle-yellow h-8 w-8 cursor-pointer disabled:opacity-50"
                            :disabled="!categorySelected || structureLocked"
                        >
                            <Icon
                                class="text-xl text-white"
                                icon="ic:outline-plus"
                            />
                        </button>
                    </div>
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
                                v-if="questions.length === 0"
                                class="divide-y divide-slate-700/50"
                            >
                                <tr class="text-sm text-slate-400 italic">
                                    <td colspan="3" class="p-4 text-center">
                                        <template v-if="!categorySelected">
                                            Selecciona una categoría
                                        </template>
                                        <template
                                            v-else-if="hiddenQuestionsCount > 0"
                                        >
                                            Sin preguntas visibles (hay
                                            {{ hiddenQuestionsCount }} ocultas —
                                            actívalo con "Ver ocultas")
                                        </template>
                                        <template v-else>
                                            Sin preguntas en esta categoría
                                        </template>
                                    </td>
                                </tr>
                            </tbody>
                            <draggable
                                v-else
                                v-model="questions"
                                item-key="id"
                                tag="tbody"
                                class="divide-y divide-slate-700/50"
                                :disabled="structureLocked"
                                @end="onReorderQuestions"
                            >
                                <template #item="{ element: question }">
                                    <tr
                                        @click="questionSelected = question.id"
                                        :class="`cursor-pointer transition-colors ${questionSelected == question.id ? 'bg-blue-600/30' : 'hover:bg-slate-600/30'} ${question.deleted_at ? 'italic opacity-50' : ''}`"
                                    >
                                        <td
                                            class="p-3 font-medium text-slate-200"
                                        >
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
                                                <template
                                                    v-if="question.deleted_at"
                                                >
                                                    <Icon
                                                        @click.stop="
                                                            restoreQuestionRow(
                                                                question.id,
                                                            )
                                                        "
                                                        class="cursor-pointer text-lg text-green-400 hover:text-green-300"
                                                        icon="ic:baseline-restore"
                                                        title="Restaurar"
                                                    />
                                                </template>
                                                <template v-else>
                                                    <Icon
                                                        v-if="!structureLocked"
                                                        @click.stop="
                                                            getQuestionToEdit(
                                                                question.id,
                                                            )
                                                        "
                                                        class="cursor-pointer text-lg text-yellow-500 hover:text-yellow-400"
                                                        icon="ic:baseline-edit"
                                                    />
                                                    <Icon
                                                        v-if="!structureLocked"
                                                        @click.stop="
                                                            deleteQuestion(
                                                                question.id,
                                                            )
                                                        "
                                                        class="cursor-pointer text-lg text-red-500 hover:text-red-400"
                                                        icon="ic:round-visibility-off"
                                                        title="Ocultar"
                                                    />
                                                </template>
                                                <Icon
                                                    v-if="isAdmin"
                                                    @click.stop="
                                                        forceDeleteQuestionRow(
                                                            question.id,
                                                        )
                                                    "
                                                    class="cursor-pointer text-lg text-red-800 hover:text-red-600"
                                                    icon="ic:baseline-delete-forever"
                                                    title="Eliminar permanentemente"
                                                />
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </draggable>
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
                        :disabled="!questionSelected || structureLocked"
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
                                v-if="
                                    !questionSelected ||
                                    answersByQuestion.length === 0
                                "
                                class="divide-y divide-slate-700/50"
                            >
                                <tr class="text-sm text-slate-400 italic">
                                    <td colspan="3" class="p-4 text-center">
                                        {{
                                            !questionSelected
                                                ? 'Selecciona una pregunta para ver sus respuestas'
                                                : 'Sin respuestas'
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                            <draggable
                                v-else
                                v-model="answersByQuestion"
                                item-key="id"
                                tag="tbody"
                                class="divide-y divide-slate-700/50 text-slate-200"
                                :disabled="structureLocked"
                                @end="onReorderAnswers"
                            >
                                <template #item="{ element: answer, index }">
                                    <tr
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
                                                    v-if="!structureLocked"
                                                    @click="
                                                        getAnswerToEdit(
                                                            answer.id,
                                                        )
                                                    "
                                                    class="cursor-pointer text-lg text-yellow-500 hover:text-yellow-400"
                                                    icon="ic:baseline-edit"
                                                />
                                                <Icon
                                                    v-if="!structureLocked"
                                                    @click="
                                                        deleteAnswer(
                                                            answer.id,
                                                            index,
                                                        )
                                                    "
                                                    class="cursor-pointer text-lg text-red-500 hover:text-red-400"
                                                    icon="ic:round-visibility-off"
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
                                                    class="cursor-pointer text-lg text-red-800 hover:text-red-600"
                                                    icon="ic:baseline-delete-forever"
                                                    title="Eliminar permanentemente"
                                                />
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </draggable>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <ActivitiesPanel
            v-if="canManageSurveys"
            :survey-id="page.props.id"
        />

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
                            <div>
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
                            <label
                                class="mt-2 flex cursor-pointer items-center gap-2 text-xs font-bold text-slate-600"
                            >
                                <input
                                    type="checkbox"
                                    v-model="formRow.allows_multiple_answers"
                                />
                                Permite selección múltiple
                            </label>
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
                            <div>
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
                </form>
            </div>
        </Modal>

        <!-- MODAL PARA CREAR/EDITAR CATEGORIES -->
        <Modal
            :show="isModalOpen_categories"
            @close="isModalOpen_categories = false"
        >
            <div class="mx-auto max-w-2xl min-w-150">
                <CategoryForm
                    :survey_id="page.props.id"
                    :category-id="editingCategoryId"
                    :next-order="categories.length + 1"
                    @update-categories="updateCategories"
                    @updated="handleCategoryUpdated"
                />
            </div>
        </Modal>
    </MainLayout>
</template>
<script setup>
import { Icon } from '@iconify/vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';
import draggable from 'vuedraggable';
import ActivitiesPanel from '@/components/ActivitiesPanel.vue';
import CategoryForm from '@/components/forms/category-form.vue';
import Modal from '@/components/modal.vue';
import NotificationBox from '@/components/notification-box.vue';

import {
    useAnswers,
    forceDeleteAnswer,
    reorderAnswers,
} from '@/composables/api/answers';
import {
    hideCategory,
    restoreCategory,
    forceDeleteCategory,
    reorderCategories,
} from '@/composables/api/categories';
import {
    createMany,
    getQuestion,
    getQuestionsByCategory,
    hideQuestion,
    restoreQuestion,
    forceDeleteQuestion,
    reorderQuestions,
    updateQuestion as updateQuestionApi,
} from '@/composables/api/questions';
import { getCategoriesBySurvey, getSurvey } from '@/composables/api/surveys';
import { useApiError } from '@/composables/useApiError';
import { useAuth } from '@/composables/useAuth';
import { useConfirm } from '@/composables/useConfirm';
import { useNotification } from '@/composables/useNotification';
import MainLayout from '@/layouts/main-layout.vue';
import { apiHost } from '@/store/store';

const { message, isError, notify } = useNotification();
const { extractErrorMessage } = useApiError();
const { isAdmin, canManageSurveys } = useAuth();
const { confirm: confirmDialog } = useConfirm();
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
const editingCategoryId = ref(0);
const showHiddenCategories = ref(false);
const showHiddenQuestions = ref(false);
const hiddenQuestionsCount = ref(0);
const questions = ref([]);
const categories = ref([]);
const survey = ref([]);
const page = usePage();
const categorySelected = ref(0);
const surveySelected = ref(0);
const questionSelected = ref(0);
const answerSelectedId = ref(0);
const answersByQuestion = ref([]);
const structureLocked = computed(() => !!survey.value?.has_results);

const formQuestion = ref([
    {
        name: '',
        order: 0,
        category_id: categorySelected.value,
        allows_multiple_answers: false,
    },
]);

const formAnswer = ref([
    {
        name: '',
        order: 0,
        question_id: 0,
    },
]);

const refreshQuestions = async () => {
    const { data: questions_ } = await getQuestionsByCategory(
        categorySelected.value,
        showHiddenQuestions.value,
    );
    questions.value = questions_;

    if (
        !showHiddenQuestions.value &&
        questions_.length === 0 &&
        categorySelected.value
    ) {
        const { data: allQuestions_ } = await getQuestionsByCategory(
            categorySelected.value,
            true,
        );
        hiddenQuestionsCount.value = allQuestions_.length;
    } else {
        hiddenQuestionsCount.value = 0;
    }
};

const refreshCategories = async () => {
    const { data } = await getCategoriesBySurvey(
        page.props.id,
        showHiddenCategories.value,
    );

    if (data) {
        categories.value = data;
    }
};

// Function to hide (soft-delete) a question
const deleteQuestion = async (id) => {
    if (
        !(await confirmDialog(
            '¿Ocultar esta pregunta? Dejará de estar disponible para responder en la app y no aparecerá en los demás apartados (categorías, reportes, etc.) hasta que la restaures.',
        ))
    ) {
        return;
    }

    const { errorFlag, responseMessage } = await hideQuestion(id);

    if (!errorFlag) {
        await refreshQuestions();
        notify('Pregunta ocultada correctamente');
    } else {
        notify(responseMessage, true);
    }
};

const restoreQuestionRow = async (id) => {
    const { errorFlag, responseMessage } = await restoreQuestion(id);

    if (!errorFlag) {
        await refreshQuestions();
        notify('Pregunta restaurada correctamente');
    } else {
        notify(responseMessage, true);
    }
};

const forceDeleteQuestionRow = async (id) => {
    if (
        !(await confirmDialog(
            '¿Eliminar esta pregunta de forma permanente? Esta acción no se puede deshacer.',
        ))
    ) {
        return;
    }

    const { errorFlag, responseMessage } = await forceDeleteQuestion(id);

    if (!errorFlag) {
        await refreshQuestions();
        notify('Pregunta eliminada permanentemente');
    } else {
        notify(responseMessage, true);
    }
};

const editCategory = (id) => {
    editingCategoryId.value = id;
    isModalOpen_categories.value = true;
};

const hideCategoryRow = async (id) => {
    if (
        !(await confirmDialog(
            '¿Ocultar esta categoría? Se ocultarán también sus preguntas y respuestas: dejarán de estar disponibles en la app y en los demás apartados hasta que las restaures.',
        ))
    ) {
        return;
    }

    const { errorFlag, responseMessage } = await hideCategory(id);

    if (!errorFlag) {
        await refreshCategories();
        notify('Categoría ocultada correctamente');
    } else {
        notify(responseMessage, true);
    }
};

const restoreCategoryRow = async (id) => {
    const { errorFlag, responseMessage } = await restoreCategory(id);

    if (!errorFlag) {
        await refreshCategories();
        notify('Categoría restaurada correctamente');
    } else {
        notify(responseMessage, true);
    }
};

const forceDeleteCategoryRow = async (id) => {
    if (
        !(await confirmDialog(
            '¿Eliminar esta categoría de forma permanente? Esta acción no se puede deshacer.',
        ))
    ) {
        return;
    }

    const { errorFlag, responseMessage } = await forceDeleteCategory(id);

    if (!errorFlag) {
        await refreshCategories();
        notify('Categoría eliminada permanentemente');
    } else {
        notify(responseMessage, true);
    }
};

const onReorderCategories = async () => {
    const snapshot = categories.value.map((category) => ({ ...category }));
    const items = categories.value.map((category, index) => ({
        id: category.id,
        order: index + 1,
    }));

    categories.value = categories.value.map((category, index) => ({
        ...category,
        order: index + 1,
    }));

    const { errorFlag, responseMessage } = await reorderCategories(items);

    if (errorFlag) {
        categories.value = snapshot;
        notify(responseMessage || 'Error al reordenar las categorías', true);
    }
};

const onReorderQuestions = async () => {
    const snapshot = questions.value.map((question) => ({ ...question }));
    const items = questions.value.map((question, index) => ({
        id: question.id,
        order: index + 1,
    }));

    questions.value = questions.value.map((question, index) => ({
        ...question,
        order: index + 1,
    }));

    const { errorFlag, responseMessage } = await reorderQuestions(items);

    if (errorFlag) {
        questions.value = snapshot;
        notify(responseMessage || 'Error al reordenar las preguntas', true);
    }
};

const onReorderAnswers = async () => {
    const snapshot = answersByQuestion.value.map((answer) => ({ ...answer }));
    const items = answersByQuestion.value.map((answer, index) => ({
        id: answer.id,
        order: index + 1,
    }));

    answersByQuestion.value = answersByQuestion.value.map((answer, index) => ({
        ...answer,
        order: index + 1,
    }));

    const { errorFlag, responseMessage } = await reorderAnswers(items);

    if (errorFlag) {
        answersByQuestion.value = snapshot;
        notify(responseMessage || 'Error al reordenar las respuestas', true);
    }
};

const forceDeleteAnswerRow = async (id, index) => {
    if (
        !(await confirmDialog(
            '¿Eliminar esta respuesta de forma permanente? Esta acción no se puede deshacer.',
        ))
    ) {
        return;
    }

    const { success, message: apiMessage } = await forceDeleteAnswer(id);

    if (success) {
        answersByQuestion.value.splice(index, 1);
        notify('Respuesta eliminada permanentemente');
    } else {
        notify(apiMessage, true);
    }
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

    const { data, errorFlag, responseMessage } = await getQuestionsByCategory(
        value,
        showHiddenQuestions.value,
    );

    if (data) {
        questions.value = data;
        answersByQuestion.value = [];

        if (!showHiddenQuestions.value && data.length === 0 && value) {
            const { data: allQuestions_ } = await getQuestionsByCategory(
                value,
                true,
            );
            hiddenQuestionsCount.value = allQuestions_.length;
        } else {
            hiddenQuestionsCount.value = 0;
        }
    } else if (errorFlag) {
        notify(responseMessage, true);
    }
});
const incrementFormRow = () => {
    formQuestion.value.push({
        name: '',
        order: questions.value.length + formQuestion.value.length + 1,
        category_id: categorySelected.value,
        allows_multiple_answers: false,
    });
};

const getQuestionToEdit = async (id) => {
    const { data, errorFlag, responseMessage } = await getQuestion(id);

    if (data) {
        formQuestion.value = [
            {
                name: data.name,
                order: data.order,
                category_id: data.category_id,
                allows_multiple_answers: data.allows_multiple_answers,
            },
        ];
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

    if (data) {
        await refreshQuestions();
        notify(data);
        isModalOpen.value = false;
        formQuestion.value = [
            {
                name: '',
                order: 0,
                category_id: categorySelected.value,
                allows_multiple_answers: false,
            },
        ];
    } else if (errorFlag) {
        notify(responseMessage, true);
    }
};

const updateQuestion = async (id) => {
    const { data, errorFlag, responseMessage } = await updateQuestionApi(
        id,
        formQuestion.value[0],
    );

    if (data) {
        await refreshQuestions();
        notify('Pregunta actualizada correctamente');
        isModalOpen.value = false;
    } else if (errorFlag) {
        notify(responseMessage, true);
    }
};

const newQuestions = () => {
    isModalOpen.value = true;
    operation_name.value = 'Crear';
    formQuestion.value = [
        {
            name: '',
            order: questions.value.length + 1,
            category_id: categorySelected.value,
            allows_multiple_answers: false,
        },
    ];
};

watch(questionSelected, async (value) => {
    router.get(
        `/surveys/details/${page.props.id}`,
        {
            categoryId: categorySelected.value,
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
const newAnswers = () => {
    isModalOpen_answers.value = true;
    operation_name.value = 'Crear';
    formAnswer.value = [
        {
            name: '',
            order: answersByQuestion.value.length + 1,
            question_id: questionSelected.value,
        },
    ];
};

const createManyAnswers = async () => {
    const { success } = await createManyAnswersApi(formAnswer.value);

    if (success) {
        const { data } = await getAnswersByQuestionApi(questionSelected.value);
        answersByQuestion.value = data;
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
    if (
        !(await confirmDialog(
            '¿Ocultar esta respuesta? Dejará de estar disponible para seleccionar en la app y no aparecerá en los demás apartados hasta que la restaures.',
        ))
    ) {
        return;
    }

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
        notify(extractErrorMessage(error), true);
    }
};
const updateAnswer = async (id) => {
    const { success } = await updateAnswerApi(id, formAnswer.value[0]);

    if (success) {
        notify('Respuesta actualizada');

        const { data } = await getAnswersByQuestionApi(questionSelected.value);
        answersByQuestion.value = data;
        isModalOpen_answers.value = false;
    } else {
        notify('Error al actualizar', true);
    }
};
const incrementFormRow_answer = () => {
    formAnswer.value.push({
        name: '',
        order: answersByQuestion.value.length + formAnswer.value.length + 1,
        question_id: questionSelected.value,
    });
};

// CATEGORIES METHODS
const handleCategoryUpdated = async () => {
    isModalOpen_categories.value = false;
    editingCategoryId.value = 0;
    await refreshCategories();
};

const updateCategories = async (status) => {
    if (status && !status.success) {
        notify(status.message || 'Error al crear la categoría', true);

        return;
    }

    if (status?.success) {
        isModalOpen_categories.value = false;
        notify(status.message || 'Categoría creada con éxito');
    }

    const { data, errorFlag, responseMessage } = await getCategoriesBySurvey(
        page.props.id,
        showHiddenCategories.value,
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
