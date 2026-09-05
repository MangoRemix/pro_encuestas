import { ref } from 'vue';
import axios from 'axios';
import { apiHost } from '@/store/store';

export function useUsers() {
    const staffData = ref({
        data: [],
        current_page: 1,
        last_page: 1,
        total: 0,
        from: 0,
        to: 0
    });
    const isLoading = ref(false);
    const errorMessage = ref('');

    const getStaff = async (page = 1) => {
        isLoading.value = true;
        errorMessage.value = '';
        try {
            const { data } = await axios.get(`${apiHost}person/pollster-admin/list?page=${page}`);
            staffData.value = data?.data ? data : {
                data: data || [],
                current_page: 1,
                last_page: 1,
                total: data?.length || 0,
                from: 1,
                to: data?.length || 0
            };
        } catch (error) {
            console.error("Error al cargar personal:", error);
            errorMessage.value = 'Error al cargar los datos. Inténtalo de nuevo.';
        } finally {
            isLoading.value = false;
        }
    };

    const deleteUser = async (id) => {
        try {
            await axios.delete(`${apiHost}person/delete/${id}`);
            await getStaff(staffData.value.current_page);
            return true;
        } catch (error) {
            console.error("Error al eliminar usuario:", error);
            errorMessage.value = 'Error al eliminar el usuario. Inténtalo de nuevo.';
            return false;
        }
    };

    const getRoleName = (rolId) => (rolId === 3 ? 'Admin' : 'Encuestador');

    return { staffData, isLoading, errorMessage, getStaff, deleteUser, getRoleName };
}
