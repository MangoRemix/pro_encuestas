const withFilters = (
    params,
    { activityId, parishId, pollsterId, dateFrom, dateTo } = {},
) => {
    if (activityId) {
        params.activity_id = activityId;
    }

    if (parishId) {
        params.parish_id = parishId;
    }

    if (pollsterId) {
        params.pollster_id = pollsterId;
    }

    if (dateFrom) {
        params.from = dateFrom;
    }

    if (dateTo) {
        params.to = dateTo;
    }

    return params;
};

/**
 * El backend responde 404 + no_relation:true cuando la parroquia y/o el
 * encuestador filtrados no tienen relación alguna con la actividad
 * elegida (en vez de simplemente devolver todo en cero). Se distingue de
 * un error real para que el frontend pueda mostrar un mensaje claro.
 */
const runReportRequest = async (request) => {
    try {
        return await request();
    } catch (e) {
        if (e.response?.status === 404 && e.response?.data?.no_relation) {
            return { data: null, noRelation: true };
        }

        console.error(e);

        return { data: null };
    }
};

export const getReportStructure = (id, filters = {}) =>
    runReportRequest(() =>
        axios.get(`/api/result/newReportStructure/${id}`, {
            params: withFilters({}, filters),
        }),
    );

export const getRespondentCountBySex = (surveyId, sexId = null, filters = {}) =>
    runReportRequest(() =>
        axios.get(`/api/result/sex/${surveyId}`, {
            params: withFilters(sexId ? { sex_id: sexId } : {}, filters),
        }),
    );

export const getRespondentCountByParish = (
    surveyId,
    parishId = null,
    filters = {},
) =>
    runReportRequest(() =>
        axios.get(`/api/result/parish/${surveyId}`, {
            params: withFilters(
                parishId ? { parish_id: parishId } : {},
                filters,
            ),
        }),
    );

export const getActivitiesForSurveyReport = async (surveyId) => {
    try {
        return await axios.get(`/api/result/activities/${surveyId}`);
    } catch (e) {
        console.error(e);

        return { data: null };
    }
};

export const getPollsterCountsForActivity = async (activityId) => {
    try {
        return await axios.get(
            `/api/result/reports/pollster-counts/${activityId}`,
        );
    } catch (e) {
        console.error(e);

        return { data: null };
    }
};
