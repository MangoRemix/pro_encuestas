// ... existing code ...
export const getReportStructure = async (surveyId) => {
    try {
        const response = await axios.get(`/api/result/newReportStructure/${surveyId}`);
        return { data: response.data, errorFlag: false };
    } catch (error) {
        return { data: null, errorFlag: true, message: error.message };
    }
};
