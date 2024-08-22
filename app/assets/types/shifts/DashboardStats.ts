import { Provider } from "../member/Provider";

export interface DashboardStats {
    provider: Provider;
    unclaimedShifts: number;
    shiftRequests: number;
    unresolvedPayments: number;
}
