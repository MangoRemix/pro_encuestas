import axios from 'axios';
import { apiHost } from '../../../store/store';

const createResponse = () => ({
    errorFlag: false,
    responseMessage: '',
    data: null,
});

export async function getQuestions() {
    const response = createResponse();

    try {
        const { data, status } = await axios.get(`${apiHost}question/show-all`);

        if (status == 200) {
            response.data = data;
        }
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage =
            error.response?.data?.message || 'Error al obtener preguntas';
    }

    return response;
}

export async function getQuestion(id) {
    const response = createResponse();

    try {
        const { data, status } = await axios.get(
            `${apiHost}question/show-one/${id}`,
        );

        if (status == 200) {
            response.data = data.question;
        }
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage =
            error.response?.data?.message || 'Error al obtener la pregunta';
    }

    return response;
}

export async function getQuestionsByCategory(category_id, withTrashed = false) {
    const response = createResponse();

    try {
        const { data, status } = await axios.get(
            `${apiHost}question/show-by-category/${category_id}`,
            { params: withTrashed ? { with_trashed: 1 } : {} },
        );

        if (status == 200) {
            response.data = data.questions;
        }
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage =
            error.response?.data?.message || 'Error al obtener las preguntas';
    }

    return response;
}

export async function getQuestionsPaginated(
    page = 1,
    { search = '', sort = '', direction = '', withTrashed = false } = {},
) {
    const response = createResponse();

    try {
        const { data, status } = await axios.get(
            `${apiHost}question/show-all`,
            {
                params: {
                    page,
                    search: search || undefined,
                    sort: sort || undefined,
                    direction: direction || undefined,
                    with_trashed: withTrashed ? 1 : undefined,
                },
            },
        );

        if (status == 200) {
            response.data = data;
        }
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage =
            error.response?.data?.message || 'Error al obtener preguntas';
    }

    return response;
}

export async function hideQuestion(id) {
    const response = createResponse();

    try {
        const { data, status } = await axios.delete(
            `${apiHost}question/delete/${id}`,
        );

        if (status == 200) {
            response.data = data;
        }
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage =
            error.response?.data?.message || 'Error al ocultar la pregunta';
    }

    return response;
}

export async function restoreQuestion(id) {
    const response = createResponse();

    try {
        const { data, status } = await axios.patch(
            `${apiHost}question/restore/${id}`,
        );

        if (status == 200) {
            response.data = data;
        }
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage =
            error.response?.data?.message || 'Error al restaurar la pregunta';
    }

    return response;
}

export async function forceDeleteQuestion(id) {
    const response = createResponse();

    try {
        const { data, status } = await axios.delete(
            `${apiHost}question/force-delete/${id}`,
        );

        if (status == 200) {
            response.data = data;
        }
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage =
            error.response?.data?.message ||
            'Error al eliminar permanentemente la pregunta';
    }

    return response;
}

export async function reorderQuestions(items) {
    const response = createResponse();

    try {
        const { data, status } = await axios.put(`${apiHost}question/reorder`, {
            items,
        });

        if (status == 200) {
            response.data = data;
        }
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage =
            error.response?.data?.message || 'Error al reordenar las preguntas';
    }

    return response;
}

export async function createMany(questions = []) {
    const response = createResponse();

    try {
        const { data, status } = await axios.post(
            `${apiHost}question/create-many`,
            questions,
        );

        if (status == 201) {
            response.data = data.message;
        }
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage =
            error.response?.data?.message || 'Error al crear las preguntas';
    }

    return response;
}

export async function updateQuestion(id, payload) {
    const response = createResponse();

    try {
        const { data, status } = await axios.put(
            `${apiHost}question/update/${id}`,
            payload,
        );

        if (status == 200) {
            response.data = data;
        }
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage =
            error.response?.data?.message || 'Error al actualizar la pregunta';
    }

    return response;
}
