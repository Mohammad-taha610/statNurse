import { defineStore } from 'pinia';

export const useUserStore = defineStore({
    id: 'user',
    state: () => ({
        user: null,
    }),
    actions: {
        setUser(user) {
            this.user = user;
        },
        toggleIsSidebarCollapsed() {
            this.isSidebarCollapsed = !this.isSidebarCollapsed;
        }
    },
    getters: {
        currentPageName(state) {
            const route = window.location.pathname;
            switch (route) {
                case '/executive/dashboard':
                    return 'Dashboard';
                case '/executive/nurses':
                    return 'Nurses';
                // TODO fill in for the rest of the routes once built
                default:
                    return 'Dashboard';
            }
        }
    }
});
