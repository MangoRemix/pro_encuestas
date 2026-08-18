import { ref } from 'vue';
import axios from 'axios';

export function useBatchProcessor() {
    const isProcessing = ref(false);

    const processBatch = async (endpoint, payload) => {
        isProcessing.value = true;
        try {
            const { data } = await axios.post(endpoint, payload);
            const batchId = data.batch_id;

            return new Promise((resolve, reject) => {
                const interval = setInterval(async () => {
                    try {
                        const { data: statusData } = await axios.get(`/api/result/batch-status/${batchId}`);
                        if (statusData.finished) {
                            clearInterval(interval);
                            isProcessing.value = false;
                            resolve(statusData.report);
                        }
                    } catch (err) {
                        clearInterval(interval);
                        isProcessing.value = false;
                        reject(err);
                    }
                }, 2000);
            });
        } catch (error) {
            isProcessing.value = false;
            throw error;
        }
    };

    return { isProcessing, processBatch };
}
