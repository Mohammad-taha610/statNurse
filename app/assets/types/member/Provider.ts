import {Nurse} from './Nurse';

export interface Provider {
    id: number;
    company: string;
    providerRoute: string;
}

export interface ProviderNurseProp {
    provider_id: number,
    nurses: Nurse[]
}
