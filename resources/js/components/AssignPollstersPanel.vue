<script setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';
import {
    assignPollsterToSurvey,
    getSurveyPollsters,
    unassignPollsterFromSurvey,
} from '@/composables/api/surveyAssignments';
import { useConfirm } from '@/composables/useConfirm';
import { useNotification } from '@/composables/useNotification';
import { apiHost } from '@/store/store';

const { surveyId } = defineProps({
    surveyId: { type: [Number, String], required: true },
});

const { notify } = useNotification();
const { confirm: confirmDialog } = useConfirm();

const allPollsters = ref([]);
const assignedPollsters = ref([]);
const history = ref([]);
const showHistory = ref(false);
const selectedPollsterId = ref('');
const loading = ref(false);

const unassignedPollsters = () => {
    const assignedIds = new Set(assignedPollsters.value.map((p) => p.id));

    return allPollsters.value.filter((p) => !assignedIds.has(p.id));
};

const loadAssigned = async () => {
    const { data, errorFlag, responseMessage } =
        await getSurveyPollsters(surveyId);

    if (errorFlag) {
        notify(responseMessage, true);

        return;
    }

    assignedPollsters.value = data;
};

const loadHistory = async () => {
    const { data, errorFlag, responseMessage } = await getSurveyPollsters(
        surveyId,
        true,
    );

    if (errorFlag) {
        notify(responseMessage, true);

        return;
    }

    history.value = data;
};

const toggleHistory = async () => {
    showHistory.value = !showHistory.value;

    if (showHistory.value) {
        await loadHistory();
    }
};

onMounted(async () => {
    loading.value = true;

    try {
        const { data } = await axios.get(
            `${apiHost}person/pollster-admin/list`,
            {
                params: { per_page: 1000 },
            },
        );
        allPollsters.value = (data?.data || []).filter(
            (person) => person.rol?.name === 'POLLSTER',
        );
    } catch {
        notify('Error al cargar la lista de encuestadores', true);
    }

    await loadAssigned();
    loading.value = false;
});

const handleAssign = async () => {
    if (!selectedPollsterId.value) {
        return;
    }

    const { errorFlag, responseMessage } = await assignPollsterToSurvey(
        surveyId,
        selectedPollsterId.value,
    );

    if (errorFlag) {
        notify(responseMessage, true);

        return;
    }

    notify('Encuestador asignado correctamente');
    selectedPollsterId.value = '';
    await loadAssigned();

    if (showHistory.value) {
        await loadHistory();
    }
};

const handleUnassign = async (person) => {
    const confirmed = await confirmDialog(
        `¿Desasignar a ${person.name} de esta encuesta?`,
    );

    if (!confirmed) {
        return;
    }

    const { errorFlag, responseMessage } = await unassignPollsterFromSurvey(
        surveyId,
        person.id,
    );

    if (errorFlag) {
        notify(responseMessage, true);

        return;
    }

    notify('Encuestador desasignado correctamente');
    await loadAssigned();

    if (showHistory.value) {
        await loadHistory();
    }
};
</script>

<template>
    <div
        class="mt-4 rounded-xl border border-blue-700/50 bg-slate-600/50 p-4 shadow-lg backdrop-blur-md"
    >
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-lg font-extrabold text-white">
                Encuestadores asignados
            </h3>
            <button
                type="button"
                class="cursor-pointer rounded-full border border-slate-500 px-2 py-1 text-xs text-slate-200 transition-colors hover:bg-slate-700/50"
                @click="toggleHistory"
            >
                {{ showHistory ? 'Ocultar historial' : 'Ver historial' }}
            </button>
        </div>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <select
                v-model="selectedPollsterId"
                class="inputs-form min-w-60 bg-white"
            >
                <option value="">Selecciona un encuestador</option>
                <option
                    v-for="pollster in unassignedPollsters()"
                    :key="pollster.id"
                    :value="pollster.id"
                >
                    {{ pollster.name }}
                </option>
            </select>
            <button
                type="button"
                :disabled="!selectedPollsterId"
                class="yellow-button-app cursor-pointer disabled:opacity-50"
                @click="handleAssign"
            >
                Asignar
            </button>
        </div>

        <div v-if="loading" class="text-sm text-slate-300">Cargando...</div>

        <ul v-else class="space-y-1">
            <li
                v-if="assignedPollsters.length === 0"
                class="text-sm text-slate-400 italic"
            >
                Ningún encuestador asignado actualmente.
            </li>
            <li
                v-for="pollster in assignedPollsters"
                :key="pollster.id"
                class="flex items-center justify-between rounded-lg bg-slate-700/50 px-3 py-2 text-slate-100"
            >
                <span>{{ pollster.name }}</span>
                <button
                    type="button"
                    class="cursor-pointer text-sm text-red-400 hover:text-red-300"
                    @click="handleUnassign(pollster)"
                >
                    Desasignar
                </button>
            </li>
        </ul>

        <div v-if="showHistory" class="mt-4 border-t border-slate-600 pt-3">
            <h4 class="mb-2 text-sm font-bold text-white">
                Historial de asignaciones
            </h4>
            <table class="w-full text-left text-sm text-slate-200">
                <thead class="text-xs tracking-wider text-slate-400 uppercase">
                    <tr>
                        <th class="p-2">Encuestador</th>
                        <th class="p-2">Asignado</th>
                        <th class="p-2">Desasignado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    <tr
                        v-for="(entry, index) in history"
                        :key="`${entry.id}-${entry.pivot.assigned_at}-${index}`"
                    >
                        <td class="p-2">{{ entry.name }}</td>
                        <td class="p-2">
                            {{
                                entry.pivot.assigned_at
                                    ? new Date(
                                          entry.pivot.assigned_at,
                                      ).toLocaleString()
                                    : '—'
                            }}
                        </td>
                        <td class="p-2">
                            {{
                                entry.pivot.unassigned_at
                                    ? new Date(
                                          entry.pivot.unassigned_at,
                                      ).toLocaleString()
                                    : 'Activo'
                            }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
