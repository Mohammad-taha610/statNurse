import {ShiftCategory} from './ShiftCategory';
import {Nurse} from '../member/Nurse';
import {Provider} from '../member/Provider';

export interface Shift {
    id: number
    start: string
    end: string
    startTime: string
    endTime: string
    provider: Provider
    date: string
    status: string
    shiftRoute: string
    nurseRoute: string
    sortingDate: string
    nurseType: string
    bonus: number
    bonusDescription: string
    incentive: number
    isCovid: boolean
    category: ShiftCategory
    nurse: Nurse
    description: string;
}

export interface CreateShiftParams {
    id?: number;
    name?: string;
    start_time: string;
    end_time: string;
    start_date: string;
    end_date?: string;
    end_date_enabled: boolean;
    nurse_type: string;
    bonus_amount: number;
    bonus_description?: string;
    recurrence_type: string;
    recurrence_interval: number;
    recurrence_end_date: string;
    recurrence_custom_dates?: string;
    description: string;
    category_id: number;
    nurse_id?: number;
    approve_nurse: boolean;
    deny_nurse: boolean;
    nurse_changed: boolean;
    number_of_copies: number;
    incentive: number;
    is_covid: string;
    is_copy: string;
    action_type: string;
    provider_id: number;
}

export interface Start {
    date: string
    timezone_type: number
    timezone: string
}


export interface ShiftCount {
    Open?: number;
    Pending?: number;
    Approved?: number;
    Assigned?: number;
    Completed?: number;
}

export interface DateShifts {
    [date: string]: ShiftsAndCountAggregate
}

export interface ShiftsAndCountAggregate {
    shifts: Shift[];
    count: ShiftCount;
}

export interface CalendarEvent {
    /**title: string;
     date: string;
     status: keyof shiftcount;
     color: any;
     shift: shift;
     start: string;
     end: string;**/
    title: string;
    shifts: Shift[];
    count: ShiftCount;
}
