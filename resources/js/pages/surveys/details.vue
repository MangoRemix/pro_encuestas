<template>
    <Head title="Encuestas: detalle"/>
    <MainLayout>
        <div class="min-h-100 w-170 py-10 mx-auto">
            <div class="text-white text-center">
                <h1 class="text-3xl mb-3 underline font-bold">{{ survey?.name }}</h1>
                <div class="flex items-center justify-center space-x-5">
                    <span>Fecha de Inicio: <b>{{ formatedDate(survey?.init_date) }}</b> </span> 
                    <span>Fecha de Fin: <b>{{ formatedDate(survey?.finish_date) }}</b> </span> 
                </div>
                
                
            </div>
            <h3 class="text-lg text-white font-bold underline text-center mt-5">Categorías registradas</h3>
            <button @click="resetFormToCreate() " class="flex items-center justify-end w-full">
                <Icon class="h-9 w-9 p-1 rounded-full text-white bg-yellow-400 cursor-pointer hover:bg-yellow-300" icon="ic:outline-plus" />
            </button>
            
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
                            <tr class="hover:bg-blue-800 transition-all duration-90 hover:text-white border border-neutral-300" v-for="(category,index) in categoriesBySurvey">
                                
                                <td class="p-2">{{ category.order }}</td>
                                <td class="p-2">{{ category.name }}</td>
                                <td class="p-2 ">
                                    <div class="w-full flex gap-x-5 justify-center align-center">
                                        <Icon @click="getCategoryToEdit(category.id) " class="text-2xl text-yellow-600 hover:text-yellow-500 cursor-pointer" icon="ic:baseline-edit"/>
                                        <Icon @click="deleteCategory(category.id,index)" class="text-2xl text-red-600 hover:text-red-500 cursor-pointer" icon="ic:baseline-restore-from-trash"/>
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
                    {{ operation_name }} categoría
                </h2>
                <form @submit.prevent="operation_name == 'Crear'?createCategory():updateCategory()" class="flex flex-col space-y-5 w-full h-full mt-10">
                    <div>
                        <label class="font-bold text-md ">Nombre categoría:</label>
                        <input v-model="form.name" required class="inputs-form " type="text" name="" id="">

                    </div>
                    <div class="flex items-center space-x-3">
                        <label class="font-bold text-md ">Orden de categoría:</label>
                        <div class="w-20">
                            <input v-model="form.order" required class="inputs-form text-center " type="number" name="" id="">
                        </div>
                        

                    </div>            
                    
                    <div class="flex justify-center w-1/3 mx-auto">
                        <button type="submit" class="primary-button-app cursor-pointer">Guardar</button>
                    </div>
                </form>
            </Modal>
            
            <NotificationBox :message="message" :isError="isError" />
        </div>
    </MainLayout>
    
    
</template>
<script setup>

import axios from 'axios';
import { Icon } from "@iconify/vue";
import { onMounted, reactive, ref } from 'vue';
import {apiHost} from '../../store/store'
import NotificationBox from '@/components/notification-box.vue';
import { Head, usePage } from '@inertiajs/vue3';
import MainLayout from '@/layouts/main-layout.vue';
import Modal from '@/components/modal.vue';
import {formatedDate} from '@/composables/shared'

const isModalOpen = ref(false);
const page = usePage()
const isError = ref(false)
const message = ref ('')
const form = reactive({
    name:'',
    order:0,
    survey_id:parseInt(page.props.id)
})
const resetFormToCreate = () =>{
    isModalOpen.value = true
    operation_name.value = 'Crear'
    form.name = ''
    form.order = 0
}
const operation_name = ref('')
const idCategoryToEdit = ref(0)
const survey = ref()


const categoriesBySurvey = ref([])

    onMounted(async ()=>{
        
        survey.value = await getSurvey(parseInt(page.props.id))
        categoriesBySurvey.value = await surveyCategories(survey?.value?.id) 
    })  

    const surveyCategories = async (survey_id)=>{
        try {
            const response = await axios.get(`${apiHost}category/show-by-survey/${survey_id}`)
            
            if(response.data.length>0)
                return response.data
        } catch (error) {
            console.log(error)         
        }
    }
    const createCategory = async () => {
        try {
            const response = await axios.post(`${apiHost}category/create`,form)
            
            if(response.status == 201){
                       
                categoriesBySurvey.value.push(response.data.category)
                isError.value = false
                message.value = response.data.message
            }
            else{
                console.log("response error",response)
            }
        } catch (error) {
            console.log(error.response)
            isError.value = true
            message.value = `Error: ${error.response.data.error}`;
            console.log(error)   
        }
    }

    const updateCategory = async () => {
        try {
            const response = await axios.put(`${apiHost}category/update/${idCategoryToEdit.value}`,form)
            
            if(response.status == 200){
                
                categoriesBySurvey.value = await surveyCategories(parseInt(page.props.id))
                isError.value = false
                message.value = response.data.message
            }
            else{
                console.log("response error",response)
            }
        } catch (error) {
            console.log(error.response)
            isError.value = true
            message.value = `Error: ${error.response.data.error}`;
            console.log(error)   
        }
    }

    const getSurvey = async (id) => {
        try {
            const response = await axios.get(`${apiHost}survey/show-one/${id}`)
            
            if(response.data.length > 0)
                return response.data[0]
            else
                return 'No hay encuestas registradas.'
        } catch (error) {
            console.log(error)   
        }
    }
    const getCategoryToEdit = async (id) => {
        isModalOpen.value = true
        operation_name.value = 'Editar'
        idCategoryToEdit.value = id
        try {
            const response = await axios.get(`${apiHost}category/show-one/${id}`)
            
            if(response.data.category){
                
                form.name = response.data?.category.name
                form.order = response.data?.category.order
            }
                
        } catch (error) {
            console.log(error)
        }
    }

    const deleteCategory = async (id,index) =>{
        try {
            const response = await axios.delete(`${apiHost}category/delete/${id}`)
            
            if(response.status == 200)
                categoriesBySurvey.value.splice(index,1)
            else
                return 'No hay encuestas registradas.'
        } catch (error) {
            console.log(error)   
        }
    }
</script>