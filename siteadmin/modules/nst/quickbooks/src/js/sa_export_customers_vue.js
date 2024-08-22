Vue.component('export-customers-view', {
    // language=HTML
    template:
        `
        <v-app>
            <v-container>
                <v-row>
                    <v-btn
                        color="primary"
                        @click="getExportCustomersRoute">Export Customers to Quickbooks</v-btn>
                </v-row>
            </v-container>
        </v-app>
    `,
    props: [],
    data: function() {
        return {

        };
    },
    created() {

    },
    mounted() {

    },
    computed: {

    },
    methods: {
        getExportCustomersRoute() {
            var data = {};
            modRequest.request('sa.quickbooks.get_export_customers_route', {}, data, function(response) {
                if(response.success) {
                    window.location.href = response.auth_url;
                } else {
                    console.log('Error');
                    console.log(response);
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        }
    }
});