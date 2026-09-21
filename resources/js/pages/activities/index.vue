<script setup>
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, reactive, ref, watch } from 'vue';
import {
    assignPollsterToActivity,
    createActivity,
    getActivities,
    getActivityPollsters,
    unassignPollsterFromActivity,
} from '@/composables/api/activities';
import { useParishes } from '@/composables/api/parishes';
import { getSurveys } from '@/composables/api/surveys';
import { formatedDate } from '@/composables/shared.js';
import { useConfirm } from '@/composables/useConfirm';
import { useNotification } from '@/composables/useNotification';
import MainLayout from '@/layouts/main-layout.vue';
import { apiHost } from '@/store/store';

const { surveyId: initialSurveyId } = defineProps({
    surveyId: { type: [Number, String], default: null },
});

const { notify } = useNotification();
const { confirm: confirmDialog } = useConfirm();
const { parishes, fetchParishes } = useParishes();

const surveys = ref([]);
const filterSurveyId = ref(initialSurveyId ? Number(initialSurveyId) : '');
const activities = ref([]);
const allPollsters = ref([]);
const pollstersByActivity = reactive({});
const selectedPollsterByActivity = reactive({});
const loading = ref(false);
const isCreating = ref(false);

const newActivity = reactive({
    survey_id: '',
    parish_id: '',
    init_date: '',
    finish_date: '',
});

const isActive = (activity) => {
    const now = Date.now();

    return (
        new Date(activity.init_date).getTime() <= now &&
        new Date(activity.finish_date).getTime() >= now
    );
};

const loadActivities = async () => {
    loading.value = true;

    const { data, errorFlag, responseMessage } = await getActivities({
        surveyId: filterSurveyId.value || undefined,
    });

    if (errorFlag) {
        notify(responseMessage, true);
        loading.value = false;

        return;
    }

    activities.value = data || [];

    await Promise.all(
        activities.value.map((activity) => loadPollsters(activity.id)),
    );

    loading.value = false;
};

const loadPollsters = async (activityId) => {
    const { data, errorFlag, responseMessage } =
        await getActivityPollsters(activityId);

    if (errorFlag) {
        notify(responseMessage, true);

        return;
    }

    pollstersByActivity[activityId] = data || [];
};

const unassignedPollstersFor = (activityId) => {
    const assignedIds = new Set(
        (pollstersByActivity[activityId] || []).map((p) => p.id),
    );

    return allPollsters.value.filter((p) => !assignedIds.has(p.id));
};

const handleCreateActivity = async () => {
    if (
        !newActivity.survey_id ||
        !newActivity.parish_id ||
        !newActivity.init_date ||
        !newActivity.finish_date
    ) {
        notify('Completa encuesta, parroquia, fecha de inicio y fecha de fin', true);

        return;
    }

    const { errorFlag, responseMessage } = await createActivity({
        survey_id: newActivity.survey_id,
        parish_id: newActivity.parish_id,
        init_date: newActivity.init_date,
        finish_date: newActivity.finish_date,
    });

    if (errorFlag) {
        notify(responseMessage, true);

        return;
    }

    notify('Actividad creada con éxito');
    newActivity.survey_id = '';
    newActivity.parish_id = '';
    newActivity.init_date = '';
    newActivity.finish_date = '';
    isCreating.value = false;
    await loadActivities();
};

const handleAssign = async (activityId) => {
    const personId = selectedPollsterByActivity[activityId];

    if (!personId) {
        return;
    }

    const { errorFlag, responseMessage } = await assignPollsterToActivity(
        activityId,
        personId,
    );

    if (errorFlag) {
        notify(responseMessage, true);

        return;
    }

    notify('Encuestador asignado correctamente');
    selectedPollsterByActivity[activityId] = '';
    await loadPollsters(activityId);
};

const handleUnassign = async (activity, person) => {
    const confirmed = await confirmDialog(
        `¿Desasignar a ${person.name} de esta actividad?`,
    );

    if (!confirmed) {
        return;
    }

    const { errorFlag, responseMessage } = await unassignPollsterFromActivity(
        activity.id,
        person.id,
    );

    if (errorFlag) {
        notify(responseMessage, true);

        return;
    }

    notify('Encuestador desasignado correctamente');
    await loadPollsters(activity.id);
};

watch(filterSurveyId, loadActivities);

onMounted(async () => {
    await fetchParishes();

    try {
        const { data } = await getSurveys({ all: true });
        surveys.value = data || [];
    } catch {
        notify('Error al cargar la lista de encuestas', true);
    }

    try {
        const { data } = await axios.get(`${apiHost}person/pollster-admin/list`, {
            params: { per_page: 1000 },
        });
        allPollsters.value = (data?.data || []).filter(
            (person) => person.rol?.name === 'POLLSTER',
        );
    } catch {
        notify('Error al cargar la lista de encuestadores', true);
    }

    await loadActivities();
});
</script>

<template>
    <Head title="Actividades" />
    <MainLayout>
        <div class="text-center">
            <h2 class="mt-8 text-3xl font-bold text-white underline">
                Actividades
            </h2>
        </div>

        <div class="mx-auto my-6 w-full max-w-4xl">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <div class="min-w-64 flex-1">
                    <label class="mb-1 block text-sm font-semibold text-slate-300">
                        Filtrar por encuesta
                    </label>
                    <select
                        v-model="filterSurveyId"
                        class="inputs-form bg-white text-gray-900"
                    >
                        <option value="">Todas las encuestas</option>
                        <option
                            v-for="survey in surveys"
                            :key="survey.id"
                            :value="survey.id"
                        >
                            {{ survey.name }}
                        </option>
                    </select>
                </div>
                <button
                    type="button"
                    class="yellow-button-app mt-6 w-auto cursor-pointer px-6"
                    @click="isCreating = !isCreating"
                >
                    {{ isCreating ? 'Cancelar' : '+ Nueva actividad' }}
                </button>
            </div>

            <div
                v-if="isCreating"
                class="mb-6 rounded-xl border border-blue-700/50 bg-slate-600/50 p-4 shadow-lg backdrop-blur-md"
            >
                <div class="flex flex-wrap items-end gap-3">
                    <div class="flex min-w-56 flex-col gap-1">
                        <label class="text-xs font-semibold text-slate-300">Encuesta</label>
                        <select
                            v-model="newActivity.survey_id"
                            class="inputs-form bg-white text-gray-900"
                        >
                            <option value="">Selecciona una encuesta</option>
                            <option
                                v-for="survey in surveys"
                                :key="survey.id"
                                :value="survey.id"
                            >
                                {{ survey.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex min-w-48 flex-col gap-1">
                        <label class="text-xs font-semibold text-slate-300">Parroquia</label>
                        <select
                            v-model="newActivity.parish_id"
                            class="inputs-form bg-white text-gray-900"
                        >
                            <option value="">Selecciona una parroquia</option>
                            <option
                                v-for="parish in parishes"
                                :key="parish.id"
                                :value="parish.id"
                            >
                                {{ parish.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-slate-300">Fecha de inicio</label>
                        <input
                            type="date"
                            v-model="newActivity.init_date"
                            class="inputs-form bg-white text-gray-900"
                        />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-slate-300">Fecha de fin</label>
                        <input
                            type="date"
                            v-model="newActivity.finish_date"
                            :min="newActivity.init_date"
                            class="inputs-form bg-white text-gray-900"
                        />
                    </div>
                    <button
                        type="button"
                        class="yellow-button-app w-auto cursor-pointer px-6"
                        @click="handleCreateActivity"
                    >
                        Crear
                    </button>
                </div>
            </div>

            <div v-if="loading" class="text-sm text-slate-300">Cargando...</div>

            <ul v-else class="space-y-3">
                <li
                    v-if="activities.length === 0"
                    class="text-sm text-slate-400 italic"
                >
                    No hay actividades para mostrar.
                </li>
                <li
                    v-for="activity in activities"
                    :key="activity.id"
                    class="rounded-lg border border-blue-700/30 bg-slate-700/50 p-4"
                >
                    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                        <div class="text-sm text-slate-100">
                            <span class="font-bold text-white">{{ activity.survey?.name }}</span>
                            <span class="mx-1 text-slate-400">·</span>
                            <span>{{ activity.parish?.name }}</span>
                            <span class="mx-1 text-slate-400">·</span>
                            <span>
                                {{ formatedDate(activity.init_date) }} —
                                {{ formatedDate(activity.finish_date) }}
                            </span>
                        </div>
                        <span
                            class="rounded-full px-2 py-0.5 text-xs font-semibold"
                            :class="
                                isActive(activity)
                                    ? 'bg-green-600/30 text-green-300'
                                    : 'bg-slate-500/30 text-slate-300'
                            "
                        >
                            {{ isActive(activity) ? 'Vigente' : 'Finalizada' }}
                        </span>
                    </div>

                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <select
                            v-model="selectedPollsterByActivity[activity.id]"
                            class="inputs-form min-w-48 bg-white text-sm text-gray-900"
                        >
                            <option value="">Selecciona un encuestador</option>
                            <option
                                v-for="pollster in unassignedPollstersFor(activity.id)"
                                :key="pollster.id"
                                :value="pollster.id"
                            >
                                {{ pollster.name }}
                            </option>
                        </select>
                        <button
                            type="button"
                            :disabled="!selectedPollsterByActivity[activity.id]"
                            class="yellow-button-app cursor-pointer text-sm disabled:opacity-50"
                            @click="handleAssign(activity.id)"
                        >
                            Asignar
                        </button>
                    </div>

                    <ul class="space-y-1">
                        <li
                            v-if="(pollstersByActivity[activity.id] || []).length === 0"
                            class="text-xs text-slate-400 italic"
                        >
                            Ningún encuestador asignado.
                        </li>
                        <li
                            v-for="pollster in pollstersByActivity[activity.id]"
                            :key="pollster.id"
                            class="flex items-center justify-between rounded bg-slate-800/50 px-2 py-1 text-sm text-slate-100"
                        >
                            <span>{{ pollster.name }}</span>
                            <button
                                type="button"
                                class="cursor-pointer text-xs text-red-400 hover:text-red-300"
                                @click="handleUnassign(activity, pollster)"
                            >
                                Desasignar
                            </button>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </MainLayout>
</template>
