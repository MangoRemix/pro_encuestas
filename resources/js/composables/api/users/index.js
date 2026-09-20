import axios from 'axios';
import { ref } from 'vue';
import { roleLabel } from '@/composables/roleLabels';
import { extractErrorMessage } from '@/composables/useApiError';
import { apiHost } from '@/store/store';

export function useUsers() {
    const staffData = ref({
        data: [],
        current_page: 1,
        last_page: 1,
        total: 0,
        from: 0,
        to: 0,
    });
    const isLoading = ref(false);
    const errorMessage = ref('');

    const getStaff = async (
        page = 1,
        { search = '', sort = '', direction = '' } = {},
    ) => {
        isLoading.value = true;
        errorMessage.value = '';

        try {
            const { data } = await axios.get(
                `${apiHost}person/pollster-admin/list`,
                {
                    params: {
                        page,
                        search: search || undefined,
                        sort: sort || undefined,
                        direction: direction || undefined,
                    },
                },
            );
            staffData.value = data?.data
                ? data
                : {
                      data: data || [],
                      current_page: 1,
                      last_page: 1,
                      total: data?.length || 0,
                      from: 1,
                      to: data?.length || 0,
                  };
        } catch (error) {
            console.error('Error al cargar personal:', error);
            errorMessage.value = extractErrorMessage(error);
        } finally {
            isLoading.value = false;
        }
    };

    const disablePerson = async (id, reason) => {
        try {
            await axios.put(`${apiHost}person/disable/${id}`, { reason });
            await getStaff(staffData.value.current_page);

            return true;
        } catch (error) {
            console.error('Error al deshabilitar usuario:', error);
            errorMessage.value = extractErrorMessage(error);

            return false;
        }
    };

    const enablePerson = async (id) => {
        try {
            await axios.put(`${apiHost}person/enable/${id}`);
            await getStaff(staffData.value.current_page);

            return true;
        } catch (error) {
            console.error('Error al habilitar usuario:', error);
            errorMessage.value = extractErrorMessage(error);

            return false;
        }
    };

    const getRoleName = (user) => roleLabel(user?.rol?.name);

    return {
        staffData,
        isLoading,
        errorMessage,
        getStaff,
        disablePerson,
        enablePerson,
        getRoleName,
    };
}
