Vue.component('sa-generate-invoice-view', {
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
                                <div class="row"> 
                                    <div class="col-xs-12 col-md-6"> 
                                        <v-autocomplete
                                            v-model="provider_id"
                                            :items="providers"
                                            item-text="name"
                                            item-value="id"
                                            label="Provider"
                                            clearable
                                            dense
                                            @change="prefillEmails"
                                        ></v-autocomplete>
                                    </div>
                                </div> 
                                <div class="row"> 
                                    <div class="col-xs-12 col-sm-6"> 
                                        <v-select
                                            v-model="pay_period"
                                            :items="pay_periods"
                                            item-text="display"
                                            item-value="combined"
                                            label="Pay Period"
                                            dense
                                        ></v-select>
                                    </div>
                                </div> 
                                <div class="row"> 
                                    <div class="col-xs-12 col-sm-6"> 
                                        <v-combobox
                                            v-model="emails"
                                            :items="provider_emails[provider_id]"
                                            label="Email"
                                            multiple
                                            chips
                                            deletable-chips
                                            clearable
                                            dense
                                            hint="Select an email or specify a new email (Press enter to add an item)"
                                            persistent-hint
                                        ></v-combobox>
                                    </div>
                                </div> 
                                <div class="row">
                                    <div class="col-xs-12 col-sm-6">
                                        <v-select
                                            v-model="review_first"
                                            :items="['Yes', 'No']"
                                            label="Review before sending emails?"
                                            dense></v-select>
                                    </div>
                                </div>
                                <div class="row"> 
                                    <div class="col-xs-12 col-sm-6">  
                                        <v-btn 
                                            :disabled="!provider_id || !pay_period"
                                            color="success"
                                            x-large
                                            @click="generateInvoice"
                                        >Generate Invoice</v-btn>
                                    </div>
                                </div>
                                <div class="row" v-if="messages.length > 0">
                                    <h2>ERRORS:</h2>
                                    <div class="col-xs-12" v-for="message in messages">
                                        <span style="display: block; color: red;">{{message}}</span>
                                    </div>
                                    <div class="col-xs-12" v-if="redirect_url">
                                        <v-btn 
                                            color="primary"
                                            :href="redirect_url">View Invoice</v-btn>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </v-app>
                </div>
            </div>
        </div>
    `,
    props: [
        '_provider_id',
        '_pay_period'
    ],
    data: function() {
        return {
            providers: [],
            provider_id: null,
            pay_periods: [],
            pay_period: '',
            provider_emails: [],
            emails: [],
            email: '',
            review_first: 'No',
            messages: [],
            redirect_url: ''
        };
    },
    created () {
        this.loadPayPeriods();
    },
    methods: {
        prefillEmails() {
            this.emails = this.provider_emails[this.provider_id];
        },
        generateInvoice() {
            var data = {
                provider_id: this.provider_id,
                pay_period: this.pay_period,
                emails: this.emails,
                review_first: this.review_first
            };

            modRequest.request('sa.invoices.generate_invoice', {}, data, function(response) {
                if(response.success) {
                    if(response.messages) {
                        this.messages = response.messages;
                        this.redirect_url = response.auth_url;
                    } else {
                        window.location.href = response.auth_url;
                    }
                } else {
                    console.log('Error');
                    console.log(response);
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        },
        loadPayPeriods() {
            modRequest.request('sa.payroll.get_pay_periods', {}, {}, function(response) {
                if(response.success) {
                    for (let i = 0; i < response.periods.length; i++) {
                        let period = response.periods[i];
                        this.pay_periods.push({
                            display: period.display,
                            combined: period.combined
                        })
                    }
                    this.loadProviders();
                } else {
                    console.log('Error');
                    console.log(response);
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        },
        loadProviders() {
            modRequest.request('sa.member.load_providers', {}, {}, function(response) {
                if(response.success) {
                    this.providers = [];
                    for (let i = 0; i < response.providers.length; i++) {
                        let provider = response.providers[i];
                        this.providers.push({
                            id: provider.id,
                            name: provider.company,
                            emails: provider.emails
                        })
                    }

                    this.provider_emails = [];
                    for (var i = 0; i < this.providers.length; i++) {
                        if(this.providers[i].emails.length) {
                            this.provider_emails[this.providers[i].id] = this.providers[i].emails;
                        } else {
                            this.provider_emails[this.providers[i].id] = [];
                        }
                    }

                    this.provider_id = parseInt(this._provider_id);
                    this.pay_period = this._pay_period;
                    this.emails = this.provider_emails[this.provider_id];
                } else {
                    console.log('Error');
                    console.log(response);
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        },
    }
});