const FALLBACK_MESSAGE = 'Ocurrió un error inesperado.';

export function extractErrorMessage(error) {
    const data = error?.response?.data;

    if (data?.errors) {
        return Object.values(data.errors).flat().join(' ');
    }

    if (data?.message) {
        return data.message;
    }

    return FALLBACK_MESSAGE;
}

export function useApiError() {
    return { extractErrorMessage };
}
