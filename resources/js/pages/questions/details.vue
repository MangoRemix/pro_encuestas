<template>
    <Head title="Preguntas: detalle" />
    
    <MainLayout>
        <NotificationBox v-if="message || isError? true:false" :message="message" :isError="isError" class="absolute z-10 right-0 top-0 w-100"/>
        <div class="min-h-100 w-270 py-10 mx-auto">
            <div class="text-white text-center">
                <h1 class="text-3xl mb-3 underline font-bold">{{ question?.name }}</h1>
            </div>
            <div class="bg-white/30 backdrop-blur-md shadow-lg rounded-xl p-6 border border-blue-700/50 w-full">
                <h3 class="text-xl text-center text-white font-extrabold mb-3">Listado de respuestas</h3>
                <div class="flex items-center justify-end w-full mb-2">
                    <button @click="newAnswers()" >
                        <Icon class="h-9 w-9 p-1 rounded-full text-white bg-yellow-400 cursor-pointer hover:bg-yellow-300" icon="ic:outline-plus" />
                    </button>
                </div>
                
                <div id="table-header" class="h-10 w-full">
                    <table class="table-fixed w-full text-left">
                        <thead>
                            <tr class="border-b border-white/30 text-white text-lg">
                                <th class="w-30">Orden</th>
                                <th>Nombre</th>
                                <th class="w-55 text-center">Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div id="table-body" class="w-full max-h-75 overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30">
                    <table class="table-fixed w-full">
                        <tbody class="text-white">
                            <tr class="border-b border-neutral-400" v-for="(answer,index) in answersByQuestion" :key="answer.id">
                                <td class="py-2 w-30">{{ answer.order }}</td>
                                <td class="py-2">{{ answer.name }}</td>
                                <td class="py-2 w-55">
                                    <div class="flex items-center justify-center gap-x-3 w-full">
                                        <Icon @click="getAnswerToEdit(answer.id) " class="text-2xl text-yellow-600 hover:text-yellow-500 cursor-pointer" icon="ic:baseline-edit"/>
                                        <Icon @click="deleteAnswer(answer.id,index)" class="text-2xl text-red-600 hover:text-red-500 cursor-pointer" icon="ic:baseline-restore-from-trash"/>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <Modal :show="isModalOpen" @close="isModalOpen = false">
                <!-- FORMULARIO RESPUESTAS -->
                
                <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">
                    {{ operation_name }} respuestas
                </h2>
                
                
                <form @submit.prevent="operation_name =='Crear'?createManyAnswers():updateAnswer(answerSelectedId)" action="" class=" w-150 min-h-50 max-h-70 overflow-y-scroll">
                    <div class="flex item-center justify-end space-x-3">
                    <button @click.prevent="incrementFormRow" class="" v-if="operation_name!='Editar'">
                        <Icon class="h-8 w-8 p-1 rounded-full bg-yellow-400 cursor-pointer hover:bg-yellow-300 text-white " icon="ic:outline-plus" />
                    </button>
                    
                    <button type="submit" class="cursor-pointer">
                        <Icon class="h-8 w-8 bg-blue-600 hover:bg-blue-700 text-xs text-white p-1 rounded-full" icon="ic:round-save" />
                    </button>
                    
                </div>
                    <div v-for="(formRow,index) in form" class="mb-3">
                        <div class="text-center font-bold mb-3">
                            <span>Respuesta {{ index+1 }}</span>
                        </div>
                        <div class="flex items-center justify-between space-x-2">
                            <div class="w-35 flex items-center space-x-2">
                                <label for="" class="text-sm font-bold">Orden: </label>
                                <input required v-model="formRow.order" min="1" type="number" class="inputs-form">
                            </div>

                            <div class="w-full flex items-center space-x-2">
                                <label for="" class="text-sm font-bold">Nombre: </label>
                                <input required minlength="5" v-model="formRow.name" type="text" class="inputs-form">
                            </div>
                        </div>
                        
                    </div>
                    
                </form>
            </Modal>
        </div>
    </MainLayout>
</template>
<script setup>
import Modal from '@/components/modal.vue';
import NotificationBox from '@/components/notification-box.vue';
import MainLayout from '@/layouts/main-layout.vue';
import { apiHost } from '@/store/store';
import { Icon } from '@iconify/vue';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref } from 'vue';

const page = usePage()

const loading = ref(false);
const message = ref('');
const isError = ref(false);

const question = ref()
const answersByQuestion = ref([])
const operation_name = ref('create')
const isModalOpen = ref(false)

const answerSelectedId = ref(0)
const form = ref([
    {
        name:'',
        order:0,
        question_id:parseInt(page.props.id)
    }
])

onMounted(async ()=>{
    question.value = await getQuestion(parseInt(page.props.id))
    if(question.value.id)
        answersByQuestion.value = await getAnswersByQuestion(question.value.id)
})

const newAnswers = ()=>{
    isModalOpen.value = true; operation_name.value = 'Crear'
    form.value[0].name = ''
    form.value[0].order = 0
}
const incrementFormRow = () =>{
    form.value.push({
        name:'',
        order:0,
        question_id:parseInt(page.props.id)
    })
}

const getQuestion = async (id) => {

    try {
        const {data,error,status} = await axios.get(`${apiHost}question/show-one/${id}`)
        
        
        if(data.question)
            return data.question
        return null
    } catch (error) {
        console.log(error)
    }
}

const getAnswersByQuestion = async (id) => {

    try {
        const {data,error} = await axios.get(`${apiHost}answer/show-by-question/${id}`)
        console.log(data)
        if(data.answers)
            return data.answers
        return null
    } catch (error) {
        console.log(error)
    }
}

const createManyAnswers = async () => {
    try {
        loading.value = true
        const {data,error,status} = await axios.post(`${apiHost}answer/create-many`,form.value)
        console.log(data)
        if(status == 201){
            getAnswersByQuestion.value.splice(index,1)
            form.value = [{
                name:'',
                order:0,
                question_id:parseInt(page.props.id)
            }]
            message.value = data.message
        }
            
        return null
    } catch (error) {
        console.log(error)
        isError.value = true
        message.value = error.response.data
    }finally{
        loading.value = false
        setTimeout(() => {
            message.value = ''
        }, 3500);
    }
}

const deleteAnswer = async (id,index) => {
    loading.value = true
    try {
        const {data,error,status} = await axios.delete(`${apiHost}answer/delete/${id}`)
        console.log(data)
        if(status == 200){
            answersByQuestion.value.splice(index,1)
            message.value = data.message
        }
        
    } catch (error) {
        isError.value = true
        message.value = error.response.message
        
    }finally{
        setTimeout(() => {
            loading.value = false
            message.value = ''    
        }, 3500);
        
    }
}

const getAnswerToEdit = async (id) => {
    try {
        const {data,error,status} = await axios.get(`${apiHost}answer/show-one/${id}`)
        if(status==200){
            
            form.value[0].name = data.name
            form.value[0].order = data.order
            isModalOpen.value = true
            operation_name.value = 'Editar'
            answerSelectedId.value = id
        }
        
    } catch (error) {
        console.log(error)
    }
}

const updateAnswer = async (id) => {
    try {
        loading.value = true
        const {data,error,status} = await axios.put(`${apiHost}answer/update/${id}`,form.value[0])
        if(status == 200){
            
            message.value = data.message
            console.log(data)
        }
    } catch (error) {
        isError.value = true
        const {response} = error
        message.value = response?.data
        
    }finally{
        loading.value = false
        setTimeout(() => {
            message.value = ''
        }, 3500);
        
    }
}

</script>