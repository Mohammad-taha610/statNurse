import {Nurse} from '../member/Nurse';
import {Provider} from '../member/Provider';

export interface ShiftPayrollPaymentAggregate {
    clockedHours: any[];  // Using any[] since we don't have the exact type.
    billRate: any[];     // Same here.
    billTotal: any[];    // And here.
    travelPay: number;
    holidayPay: number;
    bonus: number;
    date: string;
    nurse: Nurse;
    provider: Provider;
}
