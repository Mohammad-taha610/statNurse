import axios, {AxiosResponse} from 'axios';
import {Nurse} from '../types/member/Nurse';
import PresetShiftTime from '../types/shifts/PresetShiftTime';
import NurseCredential from '../types/member/NurseCredential';
import {Shift} from '../types/shifts/Shift';
import {Provider} from '../types/member/Provider';
import {Rates} from '../types/payroll/Rates';

interface LoadNursesForProviderResponse {
    nurses: Nurse[];
}

const loadNursesForProvider = async (nurseType: string, startTime?: string, endTime?: string, provider?: number): Promise<Nurse[]> => {
    const response: AxiosResponse<LoadNursesForProviderResponse> = await axios.get(`/executive/providers/nurses`, {
        params: {
            nurse_type: nurseType,
            start: startTime,
            end: endTime,
            provider: provider
        }
    });
    return response.data.nurses;
}

const blockNurse = async (nurseId: number, providers: Provider[]): Promise<void> => {
    await axios.post(`/executive/nurse/${nurseId}/block`, {
        providers: providers.map(provider => provider.id)
    });
}

const getBlockedNurses = async (): Promise<Nurse[]> => {
    const response: AxiosResponse<LoadNursesForProviderResponse> = await axios.get(`/executive/providers/nurses/blocked`);
    return response.data.nurses;
}

const unblockNurse = async (nurseId: number, providerId: number): Promise<void> => {
    await axios.post(`/executive/providers/${providerId}/nurse/${nurseId}/unblock`);
}


interface GetProviderTimeslotsResponse {
    timeslots: PresetShiftTime[];
}

const getProviderTimeslots = async (providerId: number): Promise<any> => {
    const response: AxiosResponse<GetProviderTimeslotsResponse> = await axios.get(`/executive/provider/${providerId}/timeslots`);
    return response.data.timeslots;
}

const providerCreateShift = async (data: any) => {
    const res = await axios.post(`/executive/provider/create_shift`, data);
    return [res.status, res.data];
}

interface GetProviderCredentialsResponse {
    credentials: NurseCredential[];
}

const getProviderCredentials = async (providerId: number) => {
    const response: AxiosResponse<GetProviderCredentialsResponse> = await axios.get(`/executive/provider/${providerId}/credentials`);
    return response.data.credentials;
}


interface UpcomingShiftsProps {
    shifts: Shift[];
    totalPages: number;
}

const getUpcomingShifts = async (page: number, provider: Provider | null) => {
    const res: AxiosResponse<UpcomingShiftsProps> = await axios.get(`/executive/dashboard/upcoming_shifts`, {
        params: {
            page: page,
            provider: provider?.id
        }
    });
    return res.data;
}

interface PayRateResponse {
    rates: Rates;
}
const getProviderPayRates = async (providerId: number) => {
    const res: AxiosResponse<PayRateResponse> = await axios.get(`/executive/provider/${providerId}/rates`);
    const rates = res.data.rates;
    rates.CNA.standard_pay = parseInt(rates.CNA.standard_pay.toString());
    rates.CNA.standard_bill = parseInt(rates.CNA.standard_bill.toString());
    rates.CMT.standard_pay = parseInt(rates.CMT.standard_pay.toString());
    rates.CMT.standard_bill = parseInt(rates.CMT.standard_bill.toString());
    rates.LPN.standard_pay = parseInt(rates.LPN.standard_pay.toString());
    rates.LPN.standard_bill = parseInt(rates.LPN.standard_bill.toString());
    rates.RN.standard_pay = parseInt(rates.RN.standard_pay.toString());
    rates.RN.standard_bill = parseInt(rates.RN.standard_bill.toString());
    return res.data.rates;
}

export {
    loadNursesForProvider,
    blockNurse,
    unblockNurse,
    getProviderTimeslots,
    providerCreateShift,
    getProviderCredentials,
    getUpcomingShifts,
    getBlockedNurses,
    getProviderPayRates
};
