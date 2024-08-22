import axios, {AxiosResponse} from 'axios';
import {Shift, DateShifts, CreateShiftParams} from '../types/shifts/Shift';
import {DateTime} from 'luxon';
import {ShiftPayrollPaymentAggregate} from '../types/shifts/ShiftPayrollPaymentAggregate';
import type {Filters} from '../types/types';
import {ShiftCategory} from '../types/shifts/ShiftCategory';

interface GetShiftsInDateRangeResponse {
    data: DateShifts;
}

const convertShiftToLocalTime = (shift: Shift): Shift => {
    const start = new Date(shift.start);
    const end = new Date(shift.end);

    // make new startTime with same data as start but with shift.startTime
    const startTime = new Date(shift.start);
    start.setHours(startTime.getHours());
    start.setMinutes(startTime.getMinutes());
    start.setSeconds(startTime.getSeconds());

    const endTime = new Date(shift.end);
    end.setHours(endTime.getHours());
    end.setMinutes(endTime.getMinutes());
    end.setSeconds(endTime.getSeconds());


    // get local timezone
    const newTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    // convert to local timezone
    const startString = start.toISOString();
    const endString = end.toISOString();
    const updatedStart = DateTime.fromISO(startString).setZone(newTimezone).toISO();
    const updatedEnd = DateTime.fromISO(endString).setZone(newTimezone).toISO();

    const startTimeString = startTime.toISOString();
    const endTimeString = endTime.toISOString();
    const updatedStartTime = DateTime.fromISO(startTimeString).setZone(newTimezone).toFormat('hh:mm a')
    const updatedEndTime = DateTime.fromISO(endTimeString).setZone(newTimezone).toFormat('hh:mm a')

    shift.start = updatedStart || '';
    shift.end = updatedEnd || '';
    shift.startTime = updatedStartTime || '';
    shift.endTime = updatedEndTime || '';

    return shift;
}

const getShiftsInDateRange = async (startDate: string, endDate: string, filters: Filters, calendarMode: string): Promise<DateShifts> => {
    const start = DateTime.fromISO(startDate).toUTC().toISO();
    const end = DateTime.fromISO(endDate).toUTC().toISO();
    const params: Record<string, any>= {
        start,
        end,
        calendarMode
    }

    if (filters.nurse) {
        params['nurseFilter'] = filters.nurse.id;
    }
    if (filters.provider) {
        params['providerFilter'] = filters.provider.id;
    }
    if (filters.category) {
        params['categoryFilter'] = filters.category.id;
    }
    if (filters.credential) {
        params['nurseType'] = filters.credential;
    }

    params['tz'] = Intl.DateTimeFormat().resolvedOptions().timeZone;
    const response: AxiosResponse<GetShiftsInDateRangeResponse> = await axios.get(
        `/executive/shifts/calendar/`,
        {
            params
        }
    );

    if (calendarMode === 'month') {
        return response.data.data;
    }

    const updatedData: DateShifts = {};

    // this data is grouped in the correct timezone on the backend
    const responseData = response.data.data;
    return responseData;
    for (const date in responseData) {
        if (responseData.hasOwnProperty(date)) {
            const updatedShift = responseData[date].shifts.map(shift => convertShiftToLocalTime(shift));
            updatedData[date] = {
                shifts: updatedShift,
                count: responseData[date].count
            }
        }
    }
    return updatedData;
}

const createShift = async (params: CreateShiftParams): Promise<void> => {
    const response: AxiosResponse<Shift> = await axios.post('/executive/shifts', params);
}

const approveShift = async (shift: Shift): Promise<Shift> => {
    await axios.post(`/executive/shifts/requests/${shift.id}/approve`);
    return shift;
}
const denyShift = async (shift: Shift): Promise<Shift> => {
    await axios.post(`/executive/shifts/requests/${shift.id}/deny`);
    return shift;
}

interface FetchPbjReportForPeriodResponse {
    payPeriods: ShiftPayrollPaymentAggregate[];
}
const fetchPbjReportForPeriod = async (start: string, end: string): Promise<ShiftPayrollPaymentAggregate[]> => {
    const response: AxiosResponse<FetchPbjReportForPeriodResponse> =
        await axios.get(`/executive/shifts/pbj_report/?start=${start}&end=${end}`);
    return response.data.payPeriods;
}

const getShiftCategories = async (): Promise<ShiftCategory[]> => {
    const response: AxiosResponse<ShiftCategory[]> = await axios.get('/executive/shift/categories');
    return response.data;
}

const deleteShift = async (shift: Shift): Promise<void> => {
    await axios.delete(`/executive/shift/${shift.id}`);
}

const bulkDeleteShifts = async (shiftIds: number[]) => {
    await axios.delete('/executive/shift/bulk', {data: {shiftIds}});
}

export {getShiftsInDateRange, createShift, approveShift, denyShift, fetchPbjReportForPeriod, getShiftCategories, deleteShift, bulkDeleteShifts}
