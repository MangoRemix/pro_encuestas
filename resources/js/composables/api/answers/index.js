import axios from "axios";
import {apiHost} from '../../../store/store'
const response = {
    errorFlag:false,
    responseMessage:'',
    data:null
}

export async function getAnswer(){
    try {
        const {data,error,status} = await axios.get(`${apiHost}answer/show-all`)
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

export async function getAnswersByQuestion (question_id){
        try {
            const {data,error,status} = await axios.get(`${apiHost}answer/show-by-question/${question_id}`)
            
            if(status==200){
                response.data = data.answers

            return response
        }
            
    } catch (error) {
        response.errorFlag = true
        response.responseMessage = error.response.data.message

        return response
    }
}
