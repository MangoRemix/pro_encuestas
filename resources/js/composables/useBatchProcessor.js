import axios from 'axios';
import { ref } from 'vue';

export function useBatchProcessor() {
    const isProcessing = ref(false);

    const processBatch = async (url, payload) => {
        isProcessing.value = true;
        const { data } = await axios.post(url, payload);
        console.log(payload);

        return await pollBatchStatus(data.batch_id);
    };

    const pollBatchStatus = (
        batchId,
        { intervalMs = 2000, maxAttempts = 150 } = {},
    ) => {
        return new Promise((resolve, reject) => {
            let attempts = 0;

            const interval = setInterval(async () => {
                attempts++;

                try {
                    const { data: statusData } = await axios.get(
                        `/api/result/batch-status/${batchId}`,
                    );

                    if (statusData.finished) {
                        clearInterval(interval);
                        isProcessing.value = false;
                        resolve(statusData.report);

                        return;
                    }

                    if (attempts >= maxAttempts) {
                        clearInterval(interval);
                        isProcessing.value = false;
                        reject(
                            new Error(
                                'Tiempo de espera agotado esperando el resultado del lote.',
                            ),
                        );
                    }
                } catch (err) {
                    clearInterval(interval);
                    isProcessing.value = false;
                    reject(err);
                }
            }, intervalMs);
        });
    };

    return { isProcessing, processBatch, pollBatchStatus };
}
