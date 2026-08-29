import { ref } from 'vue';
import axios from 'axios';

export function useBatchProcessor() {
    const isProcessing = ref(false);

    const processBatch = async (url, payload) => {
        isProcessing.value = true;
        const { data } = await axios.post(url, payload);
        console.log(payload)
        return await pollBatchStatus(data.batch_id);
    };

    const pollBatchStatus = (batchId) => {
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
    };

    return { isProcessing, processBatch, pollBatchStatus };
}

