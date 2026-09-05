import axios from "axios";
import {apiHost} from '../../../store/store'
export async function getSurveys({all = false}){
    
    const response = { errorFlag: false, responseMessage: '', data: null }
    try {
        
        const {data,status} = await axios.get(`${apiHost}survey/show-all`,{
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

export async function getSurveysPaginated(page = 1) {
    const response = { errorFlag: false, responseMessage: '', data: null };
    try {
        const { data, status } = await axios.get(`${apiHost}survey/show-all`, {
            params: { page }
        });
        if (status === 200) {
            response.data = data;
            return response;
        }
    } catch (error) {
        response.errorFlag = true;
        response.responseMessage = error.response?.data?.message || 'Error al obtener encuestas';
        return response;
    }
}

export async function getSurvey(id){
    const response = { errorFlag: false, responseMessage: '', data: null }
    try {
        const {data,status} = await axios.get(`${apiHost}survey/show-one/${id}`)
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
    const response = { errorFlag: false, responseMessage: '', data: null }
        try {
            const {data,status} = await axios.get(`${apiHost}category/show-by-survey/${survey_id}`)
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
    const response = { errorFlag: false, responseMessage: '', data: null }
    try {
            const {data,status} = await axios.get(`${apiHost}survey/show-full/${id}`)
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
    const response = { errorFlag: false, responseMessage: '', data: null }
    try {
        const { data, status } = await axios.post(`${apiHost}survey/import-excel`, payload)
        if (status === 200 || status === 202) {
            response.data = data
            return response
        }
    } catch (error) {
        response.errorFlag = true
        response.responseMessage = error.response?.data?.message || error.response?.data?.error || 'Error al importar la encuesta'
        return response
    }
}

