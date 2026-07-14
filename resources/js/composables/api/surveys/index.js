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
        console.log(data)
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