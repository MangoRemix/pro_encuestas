const withFilters = (params, { activityId, dateFrom, dateTo } = {}) => {
    if (activityId) {
        params.activity_id = activityId;
    }

    if (dateFrom) {
        params.from = dateFrom;
    }

    if (dateTo) {
        params.to = dateTo;
    }

    return params;
};

export const getReportStructure = async (id, filters = {}) => {
    try {
        return await axios.get(`/api/result/newReportStructure/${id}`, {
            params: withFilters({}, filters),
        });
    } catch (e) {
        console.error(e);

        return { data: null };
    }
};

export const getRespondentCountBySex = async (surveyId, sexId = null, filters = {}) => {
    try {
        const params = withFilters(sexId ? { sex_id: sexId } : {}, filters);

        return await axios.get(`/api/result/sex/${surveyId}`, { params });
    } catch (e) {
        console.error(e);

        return { data: null };
    }
};

export const getRespondentCountByParish = async (surveyId, parishId = null, filters = {}) => {
    try {
        const params = withFilters(parishId ? { parish_id: parishId } : {}, filters);

        return await axios.get(`/api/result/parish/${surveyId}`, { params });
    } catch (e) {
        console.error(e);

        return { data: null };
    }
};

export const getActivitiesForSurveyReport = async (surveyId) => {
    try {
        return await axios.get(`/api/result/activities/${surveyId}`);
    } catch (e) {
        console.error(e);

        return { data: null };
    }
};
