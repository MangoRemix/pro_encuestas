import axios from "axios";
import {apiHost} from '../../../store/store'
const response = {
    errorFlag:false,
    responseMessage:'',
    data:null
}

export async function getSurveys(){
    try {
        const {data,error,status} = await axios.get(`${apiHost}survey/show-all`)
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
