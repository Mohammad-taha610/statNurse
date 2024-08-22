export interface StandardRate {
    standard_pay: number;
    standard_bill: number;
}

export interface Rates {
    CNA: StandardRate;
    CMT: StandardRate;
    LPN: StandardRate;
    RN: StandardRate;
}
