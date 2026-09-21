import axios from 'axios';
import { extractErrorMessage } from '@/composables/useApiError';
import { apiHost } from '@/store/store';

const createResponse = () => ({
    errorFlag: false,
    responseMessage: '',
    data: null,
});

/**
 * Lista actividades, opcionalmente filtradas por encuesta. Sin surveyId
 * trae las de todas las encuestas (vista de gestión de actividades).
 */
export async function getActivities({ surveyId } = {}) {
    const response = createResponse();

    try {
        const { data } = await axios.get(`${apiHost}activity/show-all`, {
            params: surveyId ? { survey_id: surveyId } : {},
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
