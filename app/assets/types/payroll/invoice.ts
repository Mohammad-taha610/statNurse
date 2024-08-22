import {Provider} from '../member/Provider';

export interface Invoice {
    id: number;
    invoiceNumber: string;
    payPeriod: string;
    amount: number;
    status: string;
    fileUrl: string;
    provider: Provider;
}
