import axios from 'axios';
import { extractErrorMessage } from '@/composables/useApiError';
import { apiHost } from '@/store/store';

const createResponse = () => ({
    errorFlag: false,
    responseMessage: '',
    data: null,
});

export async function getSurveyPollsters(surveyId, history = false) {
    const response = createResponse();

    try {
        const { data } = await axios.get(
            `${apiHost}survey/${surveyId}/pollsters`,
            { params: history ? { history: 1 } : {} },
        );
        response.data = data;
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage = extractErrorMessage(error);
    }

    return response;
}

export async function assignPollsterToSurvey(surveyId, personId) {
    const response = createResponse();

    try {
        const { data } = await axios.post(
            `${apiHost}survey/${surveyId}/assign`,
            { person_id: personId },
        );
        response.data = data;
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage = extractErrorMessage(error);
    }

    return response;
}

export async function unassignPollsterFromSurvey(surveyId, personId) {
    const response = createResponse();

    try {
        const { data } = await axios.delete(
            `${apiHost}survey/${surveyId}/unassign/${personId}`,
        );
        response.data = data;
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage = extractErrorMessage(error);
    }

    return response;
}
