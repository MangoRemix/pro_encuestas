import axios from "axios";
import {apiHost} from '../../../store/store'
const response = {
    errorFlag:false,
    responseMessage:'',
    data:null
}

export async function getQuestion(){
    try {
        const {data,error,status} = await axios.get(`${apiHost}question/show-all`)
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

export async function getQuestionsByCategory (category_id){
        try {
            const {data,error,status} = await axios.get(`${apiHost}question/show-by-category/${category_id}`)
            
            if(status==200){
                response.data = data.questions

            return response
        }
            
    } catch (error) {
        response.errorFlag = true
        response.responseMessage = error.response.data.message

        return response
    }
}
