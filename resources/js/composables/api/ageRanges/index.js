import axios from "axios";
import { apiHost } from '../../../store/store';

const createResponse = () => ({
    errorFlag: false,
    responseMessage: '',
    data: null
});

export async function getAgeRanges() {
    const response = createResponse();
    try {
        const { data, status } = await axios.get(`${apiHost}age-range/show-all`);
        if (status === 200) {
            response.data = data;
        }
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage = error.response?.data?.error || 'Error al obtener rangos';
    }
    return response;
}

export async function saveAgeRange(payload, id = null) {
    const response = createResponse();
    try {
        const url = id ? `${apiHost}age-ranges/update/${id}` : `${apiHost}age-range/create`;
        const method = id ? 'put' : 'post';
        const { data, status } = await axios[method](url, payload);
        response.data = data;
        response.responseMessage = data.message;
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage = error.response?.data?.error || 'Error al guardar';
    }
    return response;
}

export async function deleteAgeRange(id) {
    const response = createResponse();
    try {
        const { data, status } = await axios.delete(`${apiHost}age-range/delete/${id}`);
        response.responseMessage = data.message;
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage = error.response?.data?.error || 'Error al eliminar';
    }
    return response;
}
