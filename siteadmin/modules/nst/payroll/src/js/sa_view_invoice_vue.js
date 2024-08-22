Vue.component('sa-view-invoice-vue', {
    // language=HTML
    template:
        `
        <div class="container-fluid"> 
            <div class="row"> 
                <div class="col-12"> 
                    <v-app> 
                        <div class="card"> 
                            <div class="card-header"> 
                            </div>
                            <div class="card-body">
                                <v-row>
                                    <a
                                        :disabled="btn_disabled"
                                        :href="pdf_route"
                                        target="_blank"
                                        class="btn btn-primary"
                                        ><v-icon 
                                            color="white" 
                                        >mdi-file-outline</v-icon> View PDF</a>
                                </v-row>
                                <v-row v-for="message in messages">
                                    <span style="display: block;">{{message}}</span><br>
                                </v-row>
                            </div>
                        </div>
                    </v-app>
                </div>
            </div>
        </div>
    `,
    props: [
        'id',
        'code',
        'state',
        'realm_id',
        'provider_id',
    ],
    data: function() {
        return {
            authorizeUrl: null,
            btn_disabled: true,
            pdf_route: '#',
            messages: [],
        };
    },
    created () {
        this.getAuthRoute();
        this.showInvoice();
    },
    methods: {
        getAuthRoute() {
            var data = {
                redirect_uri: 'https://portal.nursestatky.com/siteadmin/invoices/view'
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
        showInvoice() {
            var data = {
                id: this.id,
                code: this.code,
                state: this.state,
                realmId: this.realm_id,
                provider_id: this.provider_id,
            }

            modRequest.request('sa.invoices.show_invoice', {}, data, function(response) {
                if(response.success) {
                    this.pdf_route = response.file_route;
                    this.btn_disabled = false;
                    this.messages = response.messages;
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