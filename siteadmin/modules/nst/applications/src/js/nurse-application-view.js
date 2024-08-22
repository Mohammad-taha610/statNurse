window.addEventListener('load', () => {
    Vue.component('nurse-application-view', {
        template: `
            <v-tabs v-model="tab">
                <v-tab>
                    Part One
                </v-tab>

                <v-tab>
                    Part two
                </v-tab>

                <v-tabs-items v-model="tab">
                    <v-tab-item>
                        <nurse-app-part-one></nurse-app-part-one>
                    </v-tab-item>

                    <v-tab-item>
                        <nurse-app-part-two></nurse-app-part-two>
                    </v-tab-item>
                </v-tabs-items>
            </v-tabs>
        `,

        data: () => ({
            tab: '',
        }),
    })
})
