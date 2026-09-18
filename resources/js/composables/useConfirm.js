import { ref } from 'vue';

const isOpen = ref(false);
const title = ref('Confirmar acción');
const message = ref('');
let resolvePromise = null;

/**
 * Reemplazo del confirm() nativo del navegador por un modal con estilos
 * propios de la aplicación. Un único estado global (singleton) es suficiente
 * porque solo puede haber una confirmación pendiente a la vez; el componente
 * <ConfirmModal /> se monta una sola vez en main-layout.vue.
 */
export function useConfirm() {
    const confirm = (msg, opts = {}) => {
        message.value = msg;
        title.value = opts.title || 'Confirmar acción';
        isOpen.value = true;

        return new Promise((resolve) => {
            resolvePromise = resolve;
        });
    };

    const accept = () => {
        isOpen.value = false;
        resolvePromise?.(true);
        resolvePromise = null;
    };

    const cancel = () => {
        isOpen.value = false;
        resolvePromise?.(false);
        resolvePromise = null;
    };

    return { isOpen, title, message, confirm, accept, cancel };
}
