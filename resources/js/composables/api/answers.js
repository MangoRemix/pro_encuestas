import { ref } from 'vue';
import axios from 'axios';
import { apiHost } from '@/store/store';

export function useAnswers() {
    const loading = ref(false);
    const error = ref(null);
    const message = ref(null);

        const getAnswersByQuestion = async (questionId) => {
        try {
            loading.value = true;
            const { data } = await axios.get(`${apiHost}answer/show-by-question/${questionId}`);
            
            return {
                data:data.answers || []
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
            const { data } = await axios.delete(`${apiHost}answer/delete/${id}`);
            message.value = data.message;
            return true;
        } catch (e) {
            error.value = e.response?.data?.message || 'Error al eliminar';
            return false;
        } finally {
            loading.value = false;
        }
    };

    const createManyAnswers = async (payload) => {
        try {
            loading.value = true;
            const { data } = await axios.post(`${apiHost}answer/create-many`, payload);
            message.value = data.message;
            return { success: true, data };
        } catch (e) {
            error.value = e.response?.data || 'Error al crear';
            return { success: false };
        } finally {
            loading.value = false;
        }
    };

    const updateAnswer = async (id, payload) => {
        try {
            loading.value = true;
            const { data } = await axios.put(`${apiHost}answer/update/${id}`, payload);
            message.value = data.message;
            return { success: true, data };
        } catch (e) {
            error.value = e.response?.data || 'Error al actualizar';
            return { success: false };
        } finally {
            loading.value = false;
        }
    };

    return { loading, error, message, getAnswersByQuestion, deleteAnswer, createManyAnswers, updateAnswer };
}

// ── Standalone named exports ─────────────────────────────────────────────────
// step-3.vue imports these directly (not via useAnswers()).
// They mirror the functions inside useAnswers() without reactive state.

export async function getAnswersByQuestion(questionId) {
    const { data } = await axios.get(`${apiHost}answer/show-by-question/${questionId}`);
    return { data: data.answers || [] };
}

export async function updateAnswer(id, payload) {
    const { data } = await axios.put(`${apiHost}answer/update/${id}`, payload);
    return { success: true, data };
}

export async function createManyAnswers(payload) {
    const { data,status } = await axios.post(`${apiHost}answer/create-many`, payload);
    
    return { success: status == 201?true:false, data };
}
