import {Provider} from './Provider';
import {Nurse} from './Nurse';

export class ProviderLocation {
    provider: Provider
    shiftRequestCount: number
    unresolvedPaymentCount: number
    unclaimedShiftsCount: number
    currentPayPeriod: string
    previousNurses: Nurse[]
}
