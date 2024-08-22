import type {Provider} from '../member/Provider';
import {Shift} from '../shifts/Shift';

export interface PayrollPayment {
    id: number;
    nurseName: string;
    shiftId: number;
    resolvedBy: string;
    paymentId: number;
    shiftName: string;
    shiftTime: string;
    clockedHours: string;
    clockTimes: string;
    clockIn: string;
    clockOut: string;
    rate: string;
    billRate: string;
    date: string;
    amount: string;
    billAmount: string;
    type: string;
    description: string;
    requestDescription: string;
    requestClockIn: string;
    requestClockOut: string;
    actualClockIn: string;
    actualClockOut: string;
    status: string;
    supervisorName: string;
    supervisorCode: string;
    timeslip: string;
    clockInType: string;
    payHoliday: string;
    billHoliday: string;
    nurseRoute: string;
    provider: Provider;
    shift: Shift;
}

