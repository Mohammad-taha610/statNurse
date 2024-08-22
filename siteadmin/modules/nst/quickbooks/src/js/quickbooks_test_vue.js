Vue.component('quickbooks-test-view', {
    template:
    `
        <div class="container-fluid"> 
            <v-app> 
                <div class="card"> 
                    <div class="card-header"> 
                        WOOO QUICKBOOOOOOKS
                    </div>
                    <div class="card-body">
                        <v-row> 
                            <v-col cols="12"> 
                                <v-btn
                                    color="success"
                                    :href="authorizeUrl"
                                    >Authorize</v-btn>
                                <p>{{responseText}}</p>
                            </v-col>
                            <v-col cols="12"> 
                                <v-btn 
                                    color="primary"
                                    @click="runTest"
                                    >Run Test</v-btn>
                            </v-col>
                        </v-row>
                    </div>
                    <div class="card-footer">
                        AND A FOOTER 
                    </div>
                </div>
            </v-app>
        </div>
    `,
    props: [
        'code',
        'state',
        'realm_id',
    ],
    data: function() {
        return {
            responseText: '',
            authorizeUrl: null,
        };
    },
    created() {
        this.getAuthRoute();
        this.runTest();
    },
    methods: {
        getAuthRoute() {
            var data = {
                redirect_uri: 'https://portal.nursestatky.com/siteadmin/quickbooks/test'
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
        runTest() {
            var data = {
                code: this.code,
                state: this.state,
                realmId: this.realm_id
            };

            modRequest.request('sa.quickbooks.test', {}, data, function(response) {
                console.log(response);
                if(response.success) {
                    // if(response.type == 'url') {
                    //     this.authorizeUrl = response.url;
                    // }
                    this.responseText = response.message;
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