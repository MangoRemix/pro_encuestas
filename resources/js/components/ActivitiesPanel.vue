<script setup>
import axios from 'axios';
import { onMounted, reactive, ref } from 'vue';
import {
    assignPollsterToActivity,
    createActivity,
    getActivitiesForSurvey,
    getActivityPollsters,
    unassignPollsterFromActivity,
} from '@/composables/api/activities';
import { useParishes } from '@/composables/api/parishes';
import { formatedDate } from '@/composables/shared.js';
import { useConfirm } from '@/composables/useConfirm';
import { useNotification } from '@/composables/useNotification';
import { apiHost } from '@/store/store';

const { surveyId } = defineProps({
    surveyId: { type: [Number, String], required: true },
});

const { notify } = useNotification();
const { confirm: confirmDialog } = useConfirm();
const { parishes, fetchParishes } = useParishes();

const activities = ref([]);
const allPollsters = ref([]);
const pollstersByActivity = reactive({});
const selectedPollsterByActivity = reactive({});
const loading = ref(false);
const isCreating = ref(false);

const newActivity = reactive({
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

    const { data, errorFlag, responseMessage } =
        await getActivitiesForSurvey(surveyId);

    if (errorFlag) {
        notify(responseMessage, true);
        loading.value = false;

        return;
    }

    activities.value = data || [];

    await Promise.all(activities.value.map((activity) => loadPollsters(activity.id)));

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
    if (!newActivity.parish_id || !newActivity.init_date || !newActivity.finish_date) {
        notify('Completa parroquia, fecha de inicio y fecha de fin', true);

        return;
    }

    const { errorFlag, responseMessage } = await createActivity({
        survey_id: surveyId,
        parish_id: newActivity.parish_id,
        init_date: newActivity.init_date,
        finish_date: newActivity.finish_date,
    });

    if (errorFlag) {
        notify(responseMessage, true);

        return;
    }

    notify('Actividad creada con éxito');
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

onMounted(async () => {
    await fetchParishes();

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
    <div
        class="mt-4 rounded-xl border border-blue-700/50 bg-slate-600/50 p-4 shadow-lg backdrop-blur-md"
    >
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-lg font-extrabold text-white">Actividades</h3>
            <button
                type="button"
                class="cursor-pointer rounded-full border border-slate-500 px-2 py-1 text-xs text-slate-200 transition-colors hover:bg-slate-700/50"
                @click="isCreating = !isCreating"
            >
                {{ isCreating ? 'Cancelar' : '+ Nueva actividad' }}
            </button>
        </div>

        <div
            v-if="isCreating"
            class="mb-4 flex flex-wrap items-end gap-2 rounded-lg bg-slate-700/40 p-3"
        >
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-slate-300">Parroquia</label>
                <select v-model="newActivity.parish_id" class="inputs-form min-w-48 bg-white">
                    <option value="">Selecciona una parroquia</option>
                    <option v-for="parish in parishes" :key="parish.id" :value="parish.id">
                        {{ parish.name }}
                    </option>
                </select>
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-slate-300">Fecha de inicio</label>
                <input type="date" v-model="newActivity.init_date" class="inputs-form bg-white" />
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-slate-300">Fecha de fin</label>
                <input
                    type="date"
                    v-model="newActivity.finish_date"
                    :min="newActivity.init_date"
                    class="inputs-form bg-white"
                />
            </div>
            <button
                type="button"
                class="yellow-button-app cursor-pointer"
                @click="handleCreateActivity"
            >
                Crear
            </button>
        </div>

        <div v-if="loading" class="text-sm text-slate-300">Cargando...</div>

        <ul v-else class="space-y-3">
            <li
                v-if="activities.length === 0"
                class="text-sm text-slate-400 italic"
            >
                Esta encuesta todavía no tiene actividades.
            </li>
            <li
                v-for="activity in activities"
                :key="activity.id"
                class="rounded-lg bg-slate-700/50 p-3 text-slate-100"
            >
                <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                    <div class="text-sm">
                        <span class="font-bold">{{ activity.parish?.name }}</span>
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
                        {{ isActive(activity) ? 'Vigente' : 'Vencida' }}
                    </span>
                </div>

                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <select
                        v-model="selectedPollsterByActivity[activity.id]"
                        class="inputs-form min-w-48 bg-white text-sm"
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
                        class="flex items-center justify-between rounded bg-slate-800/50 px-2 py-1 text-sm"
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
</template>
