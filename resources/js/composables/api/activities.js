import axios from 'axios';
import { extractErrorMessage } from '@/composables/useApiError';
import { apiHost } from '@/store/store';

const createResponse = () => ({
    errorFlag: false,
    responseMessage: '',
    data: null,
});

/**
 * Lista actividades paginadas, opcionalmente filtradas por encuesta,
 * estado (vigente/finalizada), encuestador o fecha. Todo se resuelve en el
 * servidor en una sola consulta (incluye los encuestadores de cada
 * actividad) para no tener que pedir nada adicional por fila.
 */
export async function getActivities({
    page = 1,
    perPage = 10,
    surveyId,
    status,
    pollsterId,
    date,
} = {}) {
    const response = createResponse();

    try {
        const { data } = await axios.get(`${apiHost}activity/show-all`, {
            params: {
                page,
                per_page: perPage,
                survey_id: surveyId || undefined,
                status: status || undefined,
                pollster_id: pollsterId || undefined,
                date: date || undefined,
            },
        });
        response.data = data;
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage = extractErrorMessage(error);
    }

    return response;
}

export async function createActivity(payload) {
    const response = createResponse();

    try {
        const { data } = await axios.post(`${apiHost}activity/create`, payload);
        response.data = data;
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage = extractErrorMessage(error);
    }

    return response;
}

export async function updateActivity(activityId, payload) {
    const response = createResponse();

    try {
        const { data } = await axios.put(
            `${apiHost}activity/update/${activityId}`,
            payload,
        );
        response.data = data;
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage = extractErrorMessage(error);
    }

    return response;
}

export async function deleteActivity(activityId) {
    const response = createResponse();

    try {
        const { data } = await axios.delete(
            `${apiHost}activity/delete/${activityId}`,
        );
        response.data = data;
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage = extractErrorMessage(error);
    }

    return response;
}

export async function getActivityPollsters(activityId, history = false) {
    const response = createResponse();

    try {
        const { data } = await axios.get(
            `${apiHost}activity/${activityId}/pollsters`,
            { params: history ? { history: 1 } : {} },
        );
        response.data = data;
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage = extractErrorMessage(error);
    }

    return response;
}

export async function assignPollsterToActivity(activityId, personId) {
    const response = createResponse();

    try {
        const { data } = await axios.post(
            `${apiHost}activity/${activityId}/assign`,
            { person_id: personId },
        );
        response.data = data;
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage = extractErrorMessage(error);
    }

    return response;
}

export async function unassignPollsterFromActivity(activityId, personId) {
    const response = createResponse();

    try {
        const { data } = await axios.delete(
            `${apiHost}activity/${activityId}/unassign/${personId}`,
        );
        response.data = data;
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage = extractErrorMessage(error);
    }

    return response;
}
