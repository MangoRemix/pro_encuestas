import axios from "axios";
import { ref } from "vue";

export function useParishes() {
    const parishes = ref([]);
    const loading = ref(false);

    const fetchParishes = async () => {
        loading.value = true;
        try {
            const { data } = await axios.get('/api/parish/show-all');
            parishes.value = data;
        } finally {
            loading.value = false;
        }
    };

    const storeParish = (data) => axios.post('/api/parish/create', data);
    const updateParish = (id, data) => axios.put(`/api/parish/${id}`, data);
    const deleteParish = (id) => axios.delete(`/api/parish/${id}`);

    return { parishes, loading, fetchParishes, storeParish, updateParish, deleteParish };
}
