import {Provider} from './Provider';

export interface Nurse {
    id: number;
    firstName: string;
    lastName: string;
    fullName: string;
    provider?: Provider;
    credentials: string;
    email: string;
    phoneNumber: string;
    middleName: string;
    birthDate: string;
}
