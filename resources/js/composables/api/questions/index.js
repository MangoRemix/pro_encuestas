import axios from "axios";
import {apiHost} from '../../../store/store'
const response = {
    errorFlag:false,
    responseMessage:'',
    data:null
}

export async function getQuestions(){
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

export async function getQuestion(id){
    try {
        const {data,error,status} = await axios.get(`${apiHost}question/show-one/${id}`)
        if(status==200){
            response.data = data.question
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

export async function createMany(questions = []) {
    try {
        
        const {data,error,status} = await axios.post(`${apiHost}question/create-many`,questions)
        
        if(status == 201){
            response.data = data.message
            
            return response
        }
            
        return null
    } catch (error) {
        response.errorFlag = true
        response.responseMessage = error.response.data.message

        return response
    }
}