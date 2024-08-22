import { defineStore } from 'pinia';

export const useAppState = defineStore({
    id: 'appState',
    state: () => ({
        isSidebarCollapsed: false,
    }),
    actions: {
        toggleIsSidebarCollapsed() {
            this.isSidebarCollapsed = !this.isSidebarCollapsed;
        }
    },
    getters: {
        currentPageName(state) {
            const route = window.location.pathname;

            if (route.match(/\/executive\/provider\/\d+/)) {
                return 'Provider Location';
            }
            switch (route) {
                case '/executive/dashboard':
                    return 'Dashboard';
                case '/executive/providers/invoices':
                    return 'Invoices';
                case '/executive/shifts':
                case '/executive/shifts/create':
                case '/executive/shifts/requests':
                case '/executive/shifts/review':
                    return 'Shifts';
                case '/executive/providers/nurse_list':
                case '/executive/providers/dnr_list':
                    return 'Nurses';
                case '/executive/payroll/pbj_report':
                    return 'PBJ Report';
                case '/executive/payroll/current_pay_period':
                    return 'Current Pay Period';
                case '/executive/provider/locations':
                    return 'Locations';
                case route.startsWith('/executive/provider/'):
                    return 'Provider';
                default:
                    return 'Dashboard';
            }
        }
    }
});
