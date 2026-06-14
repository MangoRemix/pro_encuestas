import {reactive, ref} from 'vue'

export const selectedListComponentValue = ref({
    value:0
})

export const apiHost = import.meta.env.VITE_APP_API_HOST

export const selectedQuestionAnswerPoll = reactive({
    question_id:0,
    answer_id:0
})