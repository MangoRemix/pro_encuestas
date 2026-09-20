<template>
    <Head title="Encuestas: detalles" />
    <MainLayout>
        <div class="text-center">
            <h2 class="mt-8 text-3xl font-bold text-white underline">
                Encuestas
            </h2>
        </div>
        <div
            class="mb-6 flex w-full flex-col-reverse items-center justify-between gap-4 md:flex-row"
        >
            <div class="flex w-full items-center gap-2 md:w-auto">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar encuesta..."
                    @keyup.enter="handleSearch"
                    class="w-full rounded border border-slate-700 bg-slate-900 px-4 py-2 text-slate-200 placeholder-slate-500 transition-all focus:ring-1 focus:ring-slate-500 focus:outline-none md:w-80"
                />
                <button
                    @click="handleSearch"
                    class="shrink-0 rounded border border-slate-700 bg-slate-800 px-4 py-2 font-medium text-slate-200 transition-colors hover:bg-slate-700"
                >
                    Buscar
                </button>
            </div>
            <div class="flex w-full flex-wrap gap-2 md:w-auto">
                <button
                    v-if="isAdmin"
                    @click="toggleWithTrashed"
                    class="flex items-center justify-center gap-x-2 rounded border border-slate-700 bg-slate-800 px-4 py-2 font-medium text-slate-200 transition-colors hover:bg-slate-700"
                    :class="withTrashed ? 'border-blue-500 text-blue-300' : ''"
                >
                    {{ withTrashed ? 'Ocultar ocultas' : 'Mostrar ocultas' }}
                </button>
                <div class="flex w-50 items-center">
                    <Link
                        href="/surveys/create-survey/step-1"
                        class="green-button-app flex items-center justify-center gap-x-2"
                    >
                        <Icon class="text-2xl" icon="ic:outline-plus" /> Nueva
                        encuesta
                    </Link>
                </div>
            </div>
        </div>

        <!-- Vista Móvil: Tarjetas -->
        <div class="mb-4 flex flex-wrap gap-2 md:hidden">
            <button
                v-for="field in sortableFields"
                :key="field.key"
                @click="toggleSort(field.key)"
                class="rounded-full border px-3 py-1 text-xs font-medium transition-colors"
                :class="
                    sortField === field.key
                        ? 'border-blue-500 bg-blue-600 text-white'
                        : 'border-slate-700 bg-slate-800 text-slate-400'
                "
            >
                {{ field.label }}
                <span v-if="sortField === field.key">
                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                </span>
            </button>
        </div>
        <div class="space-y-4 md:hidden">
            <div
                v-for="survey in surveys"
                :key="survey.id"
                class="rounded-lg border border-slate-700 bg-slate-800 p-4 shadow-sm"
            >
                <div class="mb-3 flex items-start justify-between">
                    <h3 class="text-lg font-bold text-white">
                        {{ survey.name }}
                    </h3>
                    <span
                        class="rounded-full bg-slate-700 px-2 py-1 text-xs font-semibold text-nowrap text-slate-300"
                    >
                        {{ survey.results_count }} respuestas
                    </span>
                </div>
                <div class="mb-4 space-y-1 text-sm text-slate-400">
                    <p>Inicio: {{ formatedDate(survey.init_date) }}</p>
                    <p>Fin: {{ formatedDate(survey.finish_date) }}</p>
                </div>
                <div
                    class="flex justify-end gap-2 border-t border-slate-700 pt-3"
                >
                    <Icon
                        v-if="canManageSurveys && isSurveyExpired(survey)"
                        @click="
                            idSurveyToReactivate = survey.id;
                            isReactivateModalOpen = true;
                        "
                        class="cursor-pointer text-xl text-green-500 hover:text-green-400"
                        icon="ic:round-refresh"
                        title="Reactivar encuesta"
                    />
                    <RowActions
                        :is-admin="isAdmin"
                        :is-trashed="!!survey.deleted_at"
                        @view="router.visit(`/surveys/details/${survey.id}`)"
                        @edit="
                            idSurveyToEdit = survey.id;
                            isModalOpen = true;
                        "
                        @hide="handleHideSurvey(survey.id)"
                        @restore="handleRestoreSurvey(survey.id)"
                        @force-delete="handleForceDeleteSurvey(survey.id)"
                    />
                </div>
            </div>
        </div>

        <!-- Vista Escritorio: Tabla -->
        <div
            class="hidden overflow-hidden rounded-lg border border-slate-700 bg-gray-500/30 md:block"
        >
            <div class="custom-scrollbar max-h-150 overflow-y-auto">
                <table class="w-full border-collapse text-left">
                    <thead class="sticky top-0 z-10 bg-slate-900">
                        <tr
                            class="border-b border-slate-700 text-xs tracking-wider text-white uppercase"
                        >
                            <th
                                class="cursor-pointer p-4 select-none"
                                @click="toggleSort('name')"
                            >
                                <div class="flex items-center gap-2">
                                    Nombre
                                    <SortIcon
                                        field="name"
                                        :current-field="sortField"
                                        :direction="sortDirection"
                                    />
                                </div>
                            </th>
                            <th
                                class="cursor-pointer p-4 select-none"
                                @click="toggleSort('init_date')"
                            >
                                <div class="flex items-center gap-2">
                                    Fecha de inicio
                                    <SortIcon
                                        field="init_date"
                                        :current-field="sortField"
                                        :direction="sortDirection"
                                    />
                                </div>
                            </th>
                            <th
                                class="cursor-pointer p-4 select-none"
                                @click="toggleSort('finish_date')"
                            >
                                <div class="flex items-center gap-2">
                                    Fecha de finalización
                                    <SortIcon
                                        field="finish_date"
                                        :current-field="sortField"
                                        :direction="sortDirection"
                                    />
                                </div>
                            </th>
                            <th
                                class="cursor-pointer p-4 text-center select-none"
                                @click="toggleSort('results_count')"
                            >
                                <div
                                    class="flex items-center justify-center gap-2"
                                >
                                    Encuestados
                                    <SortIcon
                                        field="results_count"
                                        :current-field="sortField"
                                        :direction="sortDirection"
                                    />
                                </div>
                            </th>
                            <th class="p-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        <tr
                            v-for="survey in surveys"
                            :key="survey.id"
                            class="text-slate-200 transition-colors hover:bg-slate-600/30"
                        >
                            <td class="p-4 font-medium">{{ survey.name }}</td>
                            <td class="p-4">
                                {{ formatedDate(survey.init_date) }}
                            </td>
                            <td class="p-4">
                                {{ formatedDate(survey.finish_date) }}
                            </td>
                            <td class="p-4 text-center">
                                {{ survey.results_count }}
                            </td>
                            <td class="p-4">
                                <div
                                    class="flex items-center justify-center gap-3"
                                >
                                    <Icon
                                        v-if="
                                            canManageSurveys &&
                                            isSurveyExpired(survey)
                                        "
                                        @click="
                                            idSurveyToReactivate = survey.id;
                                            isReactivateModalOpen = true;
                                        "
                                        class="cursor-pointer text-xl text-green-500 hover:text-green-400"
                                        icon="ic:round-refresh"
                                        title="Reactivar encuesta"
                                    />
                                    <RowActions
                                        :is-admin="isAdmin"
                                        :is-trashed="!!survey.deleted_at"
                                        @view="
                                            router.visit(
                                                `/surveys/details/${survey.id}`,
                                            )
                                        "
                                        @edit="
                                            idSurveyToEdit = survey.id;
                                            isModalOpen = true;
                                        "
                                        @hide="handleHideSurvey(survey.id)"
                                        @restore="
                                            handleRestoreSurvey(survey.id)
                                        "
                                        @force-delete="
                                            handleForceDeleteSurvey(survey.id)
                                        "
                                    />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Modal :show="isModalOpen" @close="isModalOpen = false">
            <SurveyForm
                :surveyId="idSurveyToEdit"
                @updated="handleSurveyUpdated"
            />
        </Modal>
        <Modal
            :show="isReactivateModalOpen"
            @close="isReactivateModalOpen = false"
        >
            <SurveyForm
                :surveyId="idSurveyToReactivate"
                :is-reactivation="true"
                @updated="handleSurveyReactivated"
            />
        </Modal>
        <Pagination
            v-if="pagination && pagination.total > 0"
            :current-page="pagination.current_page"
            :last-page="pagination.last_page"
            :total="pagination.total"
            :from="pagination.from"
            :to="pagination.to"
            @page-change="getSurveys"
        />
    </MainLayout>
</template>
<script setup>
import { Icon } from '@iconify/vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

import SurveyForm from '@/components/forms/survey-form.vue';
import Modal from '@/components/modal.vue';
import Pagination from '@/components/pagination.vue';
import RowActions from '@/components/RowActions.vue';
import SortIcon from '@/components/sort-icon.vue';
import {
    getSurveysPaginated,
    hideSurvey,
    restoreSurvey,
    forceDeleteSurvey,
} from '@/composables/api/surveys';
import { formatedDate } from '@/composables/shared';
import { useAuth } from '@/composables/useAuth';
import { useConfirm } from '@/composables/useConfirm';
import { useNotification } from '@/composables/useNotification';
import MainLayout from '@/layouts/main-layout.vue';

const { notify } = useNotification();
const { isAdmin, canManageSurveys } = useAuth();
const { confirm: confirmDialog } = useConfirm();
const isModalOpen = ref(false);
const isReactivateModalOpen = ref(false);
const surveys = ref([]);
const pagination = ref(null);

const idSurveyToEdit = ref(0);
const idSurveyToReactivate = ref(0);
const searchQuery = ref('');
const sortField = ref('name');
const sortDirection = ref('asc');
const withTrashed = ref(false);
const sortableFields = [
    { key: 'name', label: 'Nombre' },
    { key: 'init_date', label: 'Inicio' },
    { key: 'finish_date', label: 'Fin' },
    { key: 'results_count', label: 'Respuestas' },
];

const toggleSort = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }

    getSurveys(1);
};

const handleSearch = () => {
    getSurveys(1);
};

const toggleWithTrashed = () => {
    withTrashed.value = !withTrashed.value;
    getSurveys(1);
};

onMounted(async () => {
    const params = new URLSearchParams(window.location.search);
    await getSurveys(parseInt(params.get('page')) || 1);
});

const getSurveys = async (page = 1) => {
    router.get(
        window.location.pathname,
        { page },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );

    const result = await getSurveysPaginated(page, {
        search: searchQuery.value,
        sort: sortField.value,
        direction: sortDirection.value,
        withTrashed: withTrashed.value,
    });

    if (!result.errorFlag && result.data) {
        surveys.value = result.data.data;
        pagination.value = result.data;
    } else {
        notify(
            result.responseMessage || 'Error al cargar las encuestas',
            'error',
        );
    }
};

const handleSurveyUpdated = async () => {
    isModalOpen.value = false;
    idSurveyToEdit.value = 0;
    await getSurveys(pagination.value?.current_page || 1);
};

const isSurveyExpired = (survey) =>
    new Date(survey.finish_date).getTime() < Date.now();

const handleSurveyReactivated = async () => {
    isReactivateModalOpen.value = false;
    idSurveyToReactivate.value = 0;
    await getSurveys(pagination.value?.current_page || 1);
};

const handleHideSurvey = async (id) => {
    const result = await hideSurvey(id);

    if (!result.errorFlag) {
        notify('Encuesta ocultada correctamente');
        await getSurveys(pagination.value?.current_page || 1);
    } else {
        notify(result.responseMessage, true);
    }
};

const handleRestoreSurvey = async (id) => {
    const result = await restoreSurvey(id);

    if (!result.errorFlag) {
        notify('Encuesta restaurada correctamente');
        await getSurveys(pagination.value?.current_page || 1);
    } else {
        notify(result.responseMessage, true);
    }
};

const handleForceDeleteSurvey = async (id) => {
    if (
        !(await confirmDialog('¿Eliminar esta encuesta de forma permanente?'))
    ) {
        return;
    }

    const result = await forceDeleteSurvey(id);

    if (!result.errorFlag) {
        notify('Encuesta eliminada permanentemente');
        await getSurveys(pagination.value?.current_page || 1);
    } else {
        notify(result.responseMessage, true);
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
