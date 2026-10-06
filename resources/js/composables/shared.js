export const formatedDate = (date) => {
    if (date) {
        return date.split('T')[0].split(' ')[0];
    }

    return '';
};
