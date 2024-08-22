import {Provider} from '../member/Provider';
import {Nurse} from '../member/Nurse';

export default interface NursePayrollPayment {
    nurseRoute: string;
    provider: Provider;
    clockedHours: number;
    payRate: number;
    billRate: number;
    payBonus: number;
    billBonus: number;
    payTravel: number;
    billTravel: number;
    payHoliday: number;
    billHoliday: number;
    payTotal: number;
    billTotal: number;
    hasUnresolvedPayments: string;
    nurse: Nurse;
}
