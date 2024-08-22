Vue.component('export-vendors-view', {
    // language=HTML
    template:
    `
        <v-app>
            <v-container>
                <v-row>
                    <v-btn
                        color="primary"
                        @click="getExportVendorsRoute">Export Vendors to Quickbooks</v-btn>
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
        getExportVendorsRoute() {
            var data = {};
            modRequest.request('sa.quickbooks.get_export_vendors_route', {}, data, function(response) {
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