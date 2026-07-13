import { ref } from 'vue';

export function useNotification() {
    const message = ref('');
    const isError = ref(false);

    const notify = (msg, error = false, duration = 3500) => {
        message.value = msg;
        isError.value = error;
        setTimeout(() => {
            message.value = '';
            isError.value = false;
        }, duration);
    };

    return { message, isError, notify };
}
