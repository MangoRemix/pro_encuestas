<template>
    <Head title="Categorías: detalle" />
    
    <MainLayout>
        <NotificationBox v-if="message || isError? true:false" :message="message" :isError="isError" class="absolute z-10 right-0 top-0 w-100"/>
        <div class="min-h-100 w-170 py-10 mx-auto">
            <div class="text-white text-center">
                <h1 class="text-3xl mb-3 underline font-bold">{{ category?.name }}</h1>
            </div>
            <h3 class="text-lg text-white font-bold underline text-center mt-5">Preguntas registradas</h3>
            <div class="flex items-center justify-end w-full">
                <button @click="newQuestions()" >
                <Icon class="h-9 w-9 p-1 rounded-full text-white bg-yellow-400 cursor-pointer hover:bg-yellow-300" icon="ic:outline-plus" />
            </button>
            </div>
            
            <div class="mt-5 w-full">
                <div id="table-header" class="w-full">
                    <table class="table-auto text-center w-full">
                        <thead class="bg-blue-900">
                            <tr class="border-b border-neutral-300">
                                
                                <th class="p-2 w-20 text-white">Orden</th>
                                <th class="p-2 text-white">Nombre</th>
                                <th class="p-2 w-45 text-white">Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div id="tale-body" class="w-full max-h-75 overflow-y-scroll">
                    <table class="w-full">
                        <tbody class="bg-white">
                            <tr class="hover:bg-blue-800 transition-all duration-90 hover:text-white border border-neutral-300" v-for="(question,index) in questionsByCategory">
                                
                                <td class="p-2">{{ question.order }}</td>
                                <td class="p-2">{{ question.name }}</td>
                                <td class="p-2 ">
                                    <div class="w-full flex gap-x-5 justify-center align-center">
                                        <Icon @click="getQuestionToEdit(question.id) " class="text-2xl text-yellow-600 hover:text-yellow-500 cursor-pointer" icon="ic:baseline-edit"/>
                                        <Icon @click="deleteQuestion(question.id,index)" class="text-2xl text-red-600 hover:text-red-500 cursor-pointer" icon="ic:baseline-restore-from-trash"/>
                                    </div>
                                    
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
            </div>
            <Modal :show="isModalOpen" @close="isModalOpen = false">
                <!-- FORMULARIO CATEGORIES -->
                
                <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">
                    {{ operation_name }} preguntas
                </h2>
                
                
                <form @submit.prevent="operation_name =='Crear'?createManyQuestions():updateQuestion(questionSelectedId)" action="" class=" w-150 min-h-50 max-h-70 overflow-y-scroll">
                    <div class="flex item-center justify-end space-x-3">
                    <button @click="incrementFormRow" class="" v-if="operation_name!='Editar'">
                        <Icon class="h-8 w-8 p-1 rounded-full bg-yellow-400 cursor-pointer hover:bg-yellow-300 text-white " icon="ic:outline-plus" />
                    </button>
                    
                    <button type="submit" class="cursor-pointer">
                        <Icon class="h-8 w-8 bg-blue-600 hover:bg-blue-700 text-xs text-white p-1 rounded-full" icon="ic:round-save" />
                    </button>
                    
                </div>
                    <div v-for="(formRow,index) in form" class="mb-3">
                        <div class="text-center font-bold mb-3">
                            <span>Pregunta {{ index+1 }}</span>
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

const category = ref()
const questionsByCategory = ref([])
const operation_name = ref('create')
const isModalOpen = ref(false)

const questionSelectedId = ref(0)
const form = ref([
    {
        name:'',
        order:0,
        category_id:parseInt(page.props.id)
    }
])

onMounted(async ()=>{
    category.value = await getCategory(parseInt(page.props.id))
    if(category.value.id)
        questionsByCategory.value = await getQuestionsByCategory(category.value.id)
})

const newQuestions = ()=>{
    isModalOpen.value = true; operation_name.value = 'Crear'
    form.value[0].name = ''
    form.value[0].order = 0
}
const incrementFormRow = () =>{
    form.value.push({
        name:'',
        order:0,
        category_id:parseInt(page.props.id)
    })
}

const getCategory = async (id) => {

    try {
        const {data,error,status} = await axios.get(`${apiHost}category/show-one/${id}`)
        console.log(data)
        
        if(data.category)
            return data.category
        return null
    } catch (error) {
        console.log(error)
    }
}

const getQuestionsByCategory = async (id) => {

    try {
        const {data,error} = await axios.get(`${apiHost}question/show-by-category/${id}`)
        console.log(data)
        if(data.questions)
            return data.questions
        return null
    } catch (error) {
        console.log(error)
    }
}

const createManyQuestions = async () => {
    try {
        loading.value = true
        const {data,error,status} = await axios.post(`${apiHost}question/create-many`,form.value)
        console.log(data)
        if(status == 201){
            questionsByCategory.value.splice(index,1)
            
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

const deleteQuestion = async (id,index) => {
    try {
        const {data,error,status} = await axios.delete(`${apiHost}question/delete/${id}`)
        console.log(data)
        if(status == 200)
            questionsByCategory.value.splice(index,1)
        return null
    } catch (error) {
        console.log(error)
    }
}

const getQuestionToEdit = async (id) => {
    try {
        const {data,error,status} = await axios.get(`${apiHost}question/show-one/${id}`)
        if(status==200){
            
            form.value[0].name = data.name
            form.value[0].order = data.order
            isModalOpen.value = true
            operation_name.value = 'Editar'
            questionSelectedId.value = id
        }
        
    } catch (error) {
        console.log(error)
    }
}

const updateQuestion = async (id) => {
    try {
        loading.value = true
        const {data,error,status} = await axios.put(`${apiHost}question/update/${id}`,form.value[0])
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