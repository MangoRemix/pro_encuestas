export const getReportStructure = async (id) => {
    try {
        return await axios.get(`/api/result/newReportStructure/${id}`);
    } catch (e) {
        console.error(e);
        return { data: null };
    }
};