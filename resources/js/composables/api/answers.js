import axios from 'axios';
import { ref } from 'vue';
import { extractErrorMessage } from '@/composables/useApiError';
import { apiHost } from '@/store/store';

export function useAnswers() {
    const loading = ref(false);
    const error = ref(null);
    const message = ref(null);

    const getAnswersByQuestion = async (questionId) => {
        try {
            loading.value = true;
            const { data } = await axios.get(
                `${apiHost}answer/show-by-question/${questionId}`,
            );

            return {
                data: data.answers || [],
            };
        } catch (e) {
            error.value = e;

            return [];
        } finally {
            loading.value = false;
        }
    };

    const deleteAnswer = async (id) => {
        try {
            loading.value = true;
            const { data } = await axios.delete(
                `${apiHost}answer/delete/${id}`,
            );
            message.value = data.message;

            return true;
        } catch (e) {
            error.value = extractErrorMessage(e);

            return false;
        } finally {
            loading.value = false;
        }
    };

    const createManyAnswers = async (payload) => {
        try {
            loading.value = true;
            const { data } = await axios.post(
                `${apiHost}answer/create-many`,
                payload,
            );
            message.value = data.message;

            return { success: true, data };
        } catch (e) {
            error.value = extractErrorMessage(e);

            return { success: false };
        } finally {
            loading.value = false;
        }
    };

    const updateAnswer = async (id, payload) => {
        try {
            loading.value = true;
            const { data } = await axios.put(
                `${apiHost}answer/update/${id}`,
                payload,
            );
            message.value = data.message;

            return { success: true, data };
        } catch (e) {
            error.value = extractErrorMessage(e);

            return { success: false };
        } finally {
            loading.value = false;
        }
    };

    return {
        loading,
        error,
        message,
        getAnswersByQuestion,
        deleteAnswer,
        createManyAnswers,
        updateAnswer,
    };
}

// ── Standalone named exports ─────────────────────────────────────────────────
// step-3.vue imports these directly (not via useAnswers()).
// They mirror the functions inside useAnswers() without reactive state.

export async function getAnswersByQuestion(questionId) {
    const { data } = await axios.get(
        `${apiHost}answer/show-by-question/${questionId}`,
    );

    return { data: data.answers || [] };
}

export async function updateAnswer(id, payload) {
    const { data } = await axios.put(`${apiHost}answer/update/${id}`, payload);

    return { success: true, data };
}

export async function createManyAnswers(payload) {
    const { data, status } = await axios.post(
        `${apiHost}answer/create-many`,
        payload,
    );

    return { success: status == 201 ? true : false, data };
}

export async function hideAnswer(id) {
    try {
        const { data } = await axios.delete(`${apiHost}answer/delete/${id}`);

        return { success: true, data };
    } catch (error) {
        return {
            success: false,
            message: extractErrorMessage(error),
        };
    }
}

export async function forceDeleteAnswer(id) {
    try {
        const { data } = await axios.delete(
            `${apiHost}answer/force-delete/${id}`,
        );

        return { success: true, data };
    } catch (error) {
        return {
            success: false,
            message: extractErrorMessage(error),
        };
    }
}

// {errorFlag, responseMessage} en vez de {success, message}: misma forma que
// reorderQuestions/reorderCategories, para que los 3 handlers de arrastre en
// surveys/details.vue sean idénticos entre sí.
export async function reorderAnswers(items) {
    try {
        const { data } = await axios.put(`${apiHost}answer/reorder`, { items });

        return { errorFlag: false, responseMessage: '', data };
    } catch (error) {
        return {
            errorFlag: true,
            responseMessage: extractErrorMessage(error),
            data: null,
        };
    }
}
