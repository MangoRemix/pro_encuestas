export const getReportStructure = async (id) => {
    try {
        return await axios.get(`/api/result/newReportStructure/${id}`);
    } catch (e) {
        console.error(e);
        return { data: null };
    }
};

export const getRespondentCountBySex = async (surveyId, sexId = null) => {
    try {
        const params = sexId ? { sex_id: sexId } : {};
        return await axios.get(`/api/result/sex/${surveyId}`, { params });
    } catch (e) {
        console.error(e);
        return { data: null };
    }
};

export const getRespondentCountByParish = async (surveyId, parishId = null) => {
    try {
        const params = parishId ? { parish_id: parishId } : {};
        return await axios.get(`/api/result/parish/${surveyId}`, { params });
    } catch (e) {
        console.error(e);
        return { data: null };
    }
};

