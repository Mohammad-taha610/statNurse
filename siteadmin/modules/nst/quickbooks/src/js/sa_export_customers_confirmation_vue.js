Vue.component('export-customers-confirmation-view', {
    // language=HTML
    template:
        `
            <v-app>
                <v-container>
                    <v-row>
                        <v-col cols="4">
                            <h1 v-if="exporting">Exporting Customers...</h1>
                            <h1 v-else>Finished Exporting {{totalExported}} providers</h1>
                            <v-progress-linear
                                    v-if="exporting"
                                    v-model="export_completion_percent"
                                    height="25"
                            >
                                <strong>{{ Math.ceil(export_completion_percent) }}%</strong>
                            </v-progress-linear>
                            
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="4">
                            <h2 v-if="error">Error</h2>
                            <h2 v-if="!error && !exporting">Success!</h2>
                        </v-col>
                    </v-row>
                    <v-row v-for="message in messages">
                        <span style="display: block">{{message}}</span><br>
                    </v-row>
                </v-container>
            </v-app>
    `,
    props: [
        'code',
        'state',
        'realm_id',
    ],
    data: function() {
        return {
            exporting: true,
            error: false,
            messages: [],
            batch: 0,
            token: '',
            export_completion_percent: 0,
            totalExported: 0,
        };
    },
    created() {
        this.exportCustomers();
    },
    mounted() {

    },
    computed: {

    },
    methods: {
        getAuthRoute() {
            var data = {
                redirect_uri: 'https://portal.nursestatky.com/siteadmin/quickbooks/export_customers_confirmation'
            };
            modRequest.request('sa.quickbooks.get_auth_route', {}, data, function(response) {
                if(response.success) {
                    this.authorizeUrl = response.url;
                } else {
                    console.log('Error');
                    console.log(response);
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        },
        exportCustomers() {
            var data = {
                code: this.code,
                state: this.state,
                realmId: this.realm_id,
                token: this.token,
                batch: this.batch,
            };
            modRequest.request('sa.quickbooks.export_customers', {}, data, function(response) {
                if(response.success) {
                    for (let i = 0; i < response.messages.length; i++) {
                        this.messages.push(response.messages[i]);
                    }

                    this.export_completion_percent = response.export_completion_percent;
                    this.batch++;
                    this.token = response.token;
                    this.finished = response.finished;
                    if(!this.finished) {
                        this.exportCustomers();
                    } else {
                        this.error = false;
                        this.exporting = false;
                        this.totalExported = response.total_exported;
                    }
                } else {
                    console.log('Error');
                    console.log(response);
                    this.exporting = false;
                    this.error = true;
                    this.message = response.message;
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        }
    }
});