import axios from "axios";
import {apiHost} from '../../../store/store'
const response = {
    errorFlag:false,
    responseMessage:'',
    data:null
}

export async function getSurveys({all = false}){
    try {
        
        const {data,error,status} = await axios.get(`${apiHost}survey/show-all`,{
            params:{
                all
            }
        })
        
        if(status==200){
            response.data = data
            return response
        }
            
    } catch (error) {
        response.errorFlag = true
        response.responseMessage = error.response.data.message

        return response
    }
}

export async function getSurvey(id){
    try {
        const {data,error,status} = await axios.get(`${apiHost}survey/show-one/${id}`)
        if(status==200){
            response.data = data
            return response
        }
            
    } catch (error) {
        response.errorFlag = true
        response.responseMessage = error.response.data.message

        return response
    }
}

export async function getCategoriesBySurvey (survey_id){
        try {
            const {data,error,status} = await axios.get(`${apiHost}category/show-by-survey/${survey_id}`)
            if(status==200){
                response.data = data

            return response
        }
            
    } catch (error) {
        response.errorFlag = true
        response.responseMessage = error.response.data.message

        return response
    }
}

export async function showFullSurvey(id){
    
    try {
            const {data,error,status} = await axios.get(`${apiHost}survey/show-full/${id}`)
            if(status==200){
                response.data = data

            return response
        }
            
    } catch (error) {
        response.errorFlag = true
        response.responseMessage = error.response.data.message

        return response
    }
}

export async function importSurveyFromExcel(payload) {
    try {
        const { data, status } = await axios.post(`${apiHost}survey/import-excel`, payload)
        if (status === 202) { // Cambiado a 202 para consistencia con el batch job
            response.data = data
            return response
        }
    } catch (error) {
        response.errorFlag = true
        response.responseMessage = error.response?.data?.message || error.response?.data?.error || 'Error al importar la encuesta'
        return response
    }
}

