import axios from 'axios';
import { apiHost } from '../../../store/store';

const createResponse = () => ({
    errorFlag: false,
    responseMessage: '',
    data: null,
});

export async function getCategoriesPaginated(
    page = 1,
    { search = '', sort = '', direction = '', withTrashed = false } = {},
) {
    const response = createResponse();

    try {
        const { data, status } = await axios.get(
            `${apiHost}category/show-all`,
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
            error.response?.data?.message || 'Error al obtener categorías';
    }

    return response;
}

export async function hideCategory(id) {
    const response = createResponse();

    try {
        const { data, status } = await axios.delete(
            `${apiHost}category/delete/${id}`,
        );

        if (status == 200) {
            response.data = data;
        }
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage =
            error.response?.data?.message || 'Error al ocultar la categoría';
    }

    return response;
}

export async function restoreCategory(id) {
    const response = createResponse();

    try {
        const { data, status } = await axios.patch(
            `${apiHost}category/restore/${id}`,
        );

        if (status == 200) {
            response.data = data;
        }
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage =
            error.response?.data?.message || 'Error al restaurar la categoría';
    }

    return response;
}

export async function forceDeleteCategory(id) {
    const response = createResponse();

    try {
        const { data, status } = await axios.delete(
            `${apiHost}category/force-delete/${id}`,
        );

        if (status == 200) {
            response.data = data;
        }
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage =
            error.response?.data?.message ||
            'Error al eliminar permanentemente la categoría';
    }

    return response;
}

export async function reorderCategories(items) {
    const response = createResponse();

    try {
        const { data, status } = await axios.put(`${apiHost}category/reorder`, {
            items,
        });

        if (status == 200) {
            response.data = data;
        }
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage =
            error.response?.data?.message ||
            'Error al reordenar las categorías';
    }

    return response;
}
