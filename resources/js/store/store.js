import {reactive, ref} from 'vue'

export const selectedListComponentValue = ref({
    value:0
})

export const apiHost = import.meta.env.VITE_APP_API_HOST

export const stepsBreadcrumb = ref(['General', 'Categorías', 'Preguntas', 'Resumen'])
export const currentStep = ref('General')