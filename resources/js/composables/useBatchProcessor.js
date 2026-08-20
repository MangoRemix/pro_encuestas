import { ref } from 'vue';
import axios from 'axios';

export function useBatchProcessor() {
    const isProcessing = ref(false);

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

    return { isProcessing, pollBatchStatus };
}

