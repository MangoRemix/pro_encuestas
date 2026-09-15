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

export async function getQuestionsByCategory(category_id) {
    const response = createResponse();

    try {
        const { data, status } = await axios.get(
            `${apiHost}question/show-by-category/${category_id}`,
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
