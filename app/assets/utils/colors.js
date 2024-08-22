const colors = {
    nursestatRed: '#ee4037',
    mainBg: '#F2F2F2',
    shiftOpen: '#5e72e4',
    shiftPending: '#fe8024',
    shiftAssigned: '#e83e8c',
    shiftApproved: '#1bd084',
    shiftCompleted: '#343a40'
}
const getColorForStatus = (status)=> {
    switch (status.toLowerCase()) {
        case 'open':
            return colors.shiftOpen;
        case 'pending':
            return colors.shiftPending;
        case 'assigned':
            return colors.shiftAssigned;
        case 'approved':
            return colors.shiftApproved;
        case 'completed':
            return colors.shiftCompleted;
        default:
            return colors.shiftOpen;

    }
};
export default colors;
export { colors, getColorForStatus };
