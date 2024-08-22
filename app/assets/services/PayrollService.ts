import axios, {AxiosResponse} from 'axios';
import {DateTime} from 'luxon';
import {PayrollPayment} from '../types/payroll/PayrollPayment';
import NursePayrollPayment from '../types/payroll/NursePayrollPayment';


interface GetPayrollPaymentsResponse {
    payments: PayrollPayment[];
}
const getPayrollPayments = async (
    payPeriodStart: string,
    payPeriodEnd: string,
    unresolvedOnly: boolean,
) => {
    const parsedStart = DateTime.fromISO(payPeriodStart).toUTC().toISO()?.toString();
    const parsedEnd = DateTime.fromISO(payPeriodEnd).toUTC().toISO()?.toString();
    const url = `/executive/payroll/payments?payPeriodStart=${parsedStart}&payPeriodEnd=${parsedEnd}&unresolvedOnly=${unresolvedOnly}`;
    const response: AxiosResponse<GetPayrollPaymentsResponse> = await axios.get(url);

    const timeToLocal = (timeString: string) => {
        const dtUTC = DateTime.fromFormat(timeString, 'h:mm a', { zone: 'UTC' })
        const dtLocal = dtUTC.toLocal()
        return dtLocal.toFormat('hh:mm a')
    }

    return response.data.payments.map((payment) => {
        return {
            ...payment,
            actualClockIn: timeToLocal(payment.actualClockIn),
            actualClockOut: timeToLocal(payment.actualClockOut),
        }
    });
}

interface GetNursePayrollPaymentsResponse {
    payments: NursePayrollPayment[];
}

const getNursePayrollPayments = async (
    payPeriodStart: string,
    payPeriodEnd: string,
    unresolvedOnly: boolean,
) => {
    const parsedStart = DateTime.fromISO(payPeriodStart).toUTC().toISO()?.toString();
    const parsedEnd = DateTime.fromISO(payPeriodEnd).toUTC().toISO()?.toString();
    const url = `/executive/payroll/nurse_payments?payPeriodStart=${parsedStart}&payPeriodEnd=${parsedEnd}&unresolvedOnly=${unresolvedOnly}`;
    const response: AxiosResponse<GetNursePayrollPaymentsResponse> = await axios.get(url);
    return response.data.payments;
}

const requestPayrollChange = async (
    payrollPaymentId: number,
    description: string,
    clockIn: string,
    clockOut: string,

) => {
    const url = `/executive/payroll/payments/${payrollPaymentId}/request_change`;
       const response: AxiosResponse<GetNursePayrollPaymentsResponse> = await axios.post(url, {
        description,
        clockIn,
        clockOut,
    });
}

export {getPayrollPayments, getNursePayrollPayments, requestPayrollChange}
